@extends('admin.layouts.app')

@section('title', 'Create Email Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('email-templates.index') }}">Email Templates</a></li>
    <li class="breadcrumb-item active">Create Template</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-envelope me-2 text-primary"></i>Create Email Template</h1>
        <p class="page-subtitle text-muted">Create a new email template for automated email communications</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createEmailTemplateForm" action="{{ contextRoute('email-templates.store') }}" method="POST">
                @csrf
                
                <!-- Template Basic Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Template Information</h4>
                        <small class="opacity-75">Basic template details and configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Template Name *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="appointment_confirmation" required>
                                    <div class="form-help">Use lowercase letters, numbers, and underscores only</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">
                                        <i class="fas fa-folder me-1"></i>Category *
                                    </label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="appointment" {{ old('category') == 'appointment' ? 'selected' : '' }}>Appointment</option>
                                        <option value="notification" {{ old('category') == 'notification' ? 'selected' : '' }}>Notification</option>
                                        <option value="welcome" {{ old('category') == 'welcome' ? 'selected' : '' }}>Welcome</option>
                                        <option value="reminder" {{ old('category') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                        <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Billing</option>
                                        <option value="pharmacy" {{ old('category') == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                                        <option value="emergency" {{ old('category') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                        <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-text-width me-1"></i>Email Subject *
                                    </label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject') }}" 
                                           placeholder="Your Appointment Confirmation" required>
                                    <div class="form-help">Email subject line (can include variables)</div>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status *
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sender_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Sender Name
                                    </label>
                                    <input type="text" class="form-control @error('sender_name') is-invalid @enderror" 
                                           id="sender_name" name="sender_name" value="{{ old('sender_name') }}" 
                                           placeholder="ThanksDoc EHR">
                                    <div class="form-help">Leave blank to use system default</div>
                                    @error('sender_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sender_email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Sender Email
                                    </label>
                                    <input type="email" class="form-control @error('sender_email') is-invalid @enderror" 
                                           id="sender_email" name="sender_email" value="{{ old('sender_email') }}" 
                                           placeholder="noreply@hospital.com">
                                    <div class="form-help">Leave blank to use system default</div>
                                    @error('sender_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2" 
                                      placeholder="Brief description of this template">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Email Content-->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-code me-2"></i>Email Content</h4>
                        <small class="opacity-75">Template body and HTML content configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="body" class="form-label">
                                <i class="fas fa-edit me-1"></i>Email Content *
                            </label>
                            
                            <!-- Professional Rich Text Editor -->
                            <div id="editor-container" class="border rounded" style="background: white;">
                                <div id="toolbar" class="d-flex flex-wrap align-items-center p-2 border-bottom bg-light">
                                    <!-- Text Formatting -->
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('bold')" title="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('italic')" title="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('underline')" title="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Font Size -->
                                    <div class="btn-group me-2" role="group">
                                        <select class="form-select form-select-sm" onchange="changeFontSize(this.value)" title="Font Size">
                                            <option value="1">8pt</option>
                                            <option value="2">10pt</option>
                                            <option value="3" selected>12pt</option>
                                            <option value="4">14pt</option>
                                            <option value="5">18pt</option>
                                            <option value="6">24pt</option>
                                            <option value="7">36pt</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Text Color -->
                                    <div class="btn-group me-2" role="group">
                                        <div class="color-picker-wrapper">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Text Color">
                                                <i class="fas fa-font" id="text-color-icon" style="color: #000000;"></i>
                                            </button>
                                            <input type="color" id="text-color" class="color-input" value="#000000" onchange="changeTextColor(this.value)">
                                        </div>
                                    </div>
                                    
                                    <!-- Background Color -->
                                    <div class="btn-group me-2" role="group">
                                        <div class="color-picker-wrapper">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Background Color">
                                                <i class="fas fa-fill-drip" id="bg-color-icon" style="color: #ffffff;"></i>
                                            </button>
                                            <input type="color" id="bg-color" class="color-input" value="#ffffff" onchange="changeBackgroundColor(this.value)">
                                        </div>
                                    </div>
                                    
                                    <!-- Alignment -->
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('justifyLeft')" title="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('justifyCenter')" title="Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('justifyRight')" title="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Lists -->
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('insertUnorderedList')" title="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="execCmd('insertOrderedList')" title="Numbered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Image -->
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showImageDialog()" title="Insert Image">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Table -->
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTable()" title="Insert Table">
                                            <i class="fas fa-table"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- HTML Source -->
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleSource" title="Toggle HTML Source">
                                            <i class="fas fa-code"></i> HTML
                                        </button>
                                    </div>
                                    
                                    <div class="badge bg-info ms-auto">Rich Text Editor</div>
                                </div>
                                
                                <!-- Visual Editor -->
                                <div id="visual-editor" contenteditable="true" style="min-height: 300px; padding: 15px; outline: none; font-family: Arial, sans-serif; line-height: 1.6;">
                                    <!-- Content will be loaded here -->
                                </div>
                                
                                <!-- HTML Source Editor (hidden by default) -->
                                <div id="source-editor" style="display: none;">
                                    <textarea class="form-control" id="html-source" rows="15" style="font-family: 'Courier New', monospace; font-size: 13px; border: none; outline: none; resize: vertical;"></textarea>
                                </div>
                            </div>
                            
                            <textarea name="body" id="body" style="display: none;">{{ old('body', '') }}</textarea>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="form-help">
                                    You can use variables like {!!'{{'!!}patient_name{!!'}}'!!}, {!!'{{'!!}doctor_name{!!'}}'!!}, {!!'{{'!!}appointment_date{!!'}}'!!}, etc. <br>
                                    <strong>Note:</strong> Click "View Template" after creating to preview with sample data.
                                </small>
                            </div>
                            @error('body')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-section text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-save me-2"></i>Create Template
                    </button>
                    <a href="{{ contextRoute('email-templates.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            @include('admin.communication.email-templates.shortcodes-sidebar')
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        border: 1px solid #e3e6f0;
    }
    
    .form-section-header {
        background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
    }
    
    .form-section-body {
        padding: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #1cc88a;
        box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
    }
    
    .form-help {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.5rem;
        font-style: italic;
    }
    
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .variable-list {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .variable-category {
        margin-bottom: 1.5rem;
    }
    
    .variable-category h6 {
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .variable-item {
        padding: 0.5rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .variable-item:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }
    
    .variable-item code {
        color: #e83e8c;
        font-size: 0.875rem;
        font-weight: 500;
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
    
    .btn-outline-primary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-secondary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    /* Visual Editor Table Styling */
    #visual-editor table {
        display: table !important;
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 10px 0 !important;
        border: 1px solid #ddd !important;
        table-layout: auto !important;
    }
    
    #visual-editor table thead {
        display: table-header-group !important;
    }
    
    #visual-editor table tbody {
        display: table-row-group !important;
    }
    
    #visual-editor table tr {
        display: table-row !important;
    }
    
    #visual-editor table td,
    #visual-editor table th {
        display: table-cell !important;
        border: 1px solid #ddd !important;
        padding: 12px !important;
        vertical-align: top !important;
        min-width: 50px !important;
        min-height: 20px !important;
    }
    
    #visual-editor table th {
        background-color: #f8f9fa !important;
        font-weight: bold !important;
        text-align: center !important;
    }
    
    #visual-editor table td {
        text-align: left !important;
    }
    
    /* Ensure all HTML elements display properly */
    #visual-editor p {
        display: block !important;
        margin: 8px 0 !important;
        line-height: 1.6 !important;
    }
    
    #visual-editor h1, #visual-editor h2, #visual-editor h3, 
    #visual-editor h4, #visual-editor h5, #visual-editor h6 {
        display: block !important;
        margin: 20px 0 10px 0 !important;
        font-weight: bold !important;
        line-height: 1.3 !important;
    }
    
    #visual-editor ul, #visual-editor ol {
        display: block !important;
        margin: 10px 0 !important;
        padding-left: 30px !important;
    }
    
    #visual-editor li {
        display: list-item !important;
        margin: 4px 0 !important;
    }
    
    #visual-editor strong {
        font-weight: bold !important;
    }
    
    #visual-editor em {
        font-style: italic !important;
    }
    
    #visual-editor div {
        display: block !important;
    }
    
    /* Table styling for preview */
    #preview-content table {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 10px 0 !important;
        border: 1px solid #ddd !important;
    }
    
    #preview-content table th {
        border: 1px solid #ddd !important;
        padding: 8px 12px !important;
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
        text-align: center !important;
    }
    
    #preview-content table td {
        border: 1px solid #ddd !important;
        padding: 8px 12px !important;
    }
    
    #preview-content p {
        margin: 8px 0;
    }
    
    #preview-content strong {
        font-weight: bold;
    }
    
    #preview-content em {
        font-style: italic;
    }
    
    #preview-content h1, #preview-content h2, #preview-content h3,
    #preview-content h4, #preview-content h5, #preview-content h6 {
        font-weight: bold;
        margin: 12px 0 8px 0;
    }
    
    #preview-content ul, #preview-content ol {
        margin: 8px 0;
        padding-left: 30px;
    }
    
    #preview-content li {
        margin: 4px 0;
    }
    
    /* Color Picker Styles */
    .color-picker-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .color-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    /* Image Modal Styles */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .image-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .modal-close:hover {
        color: #000;
    }
    
    .image-tabs {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .image-tab {
        padding: 10px 20px;
        cursor: pointer;
        border: none;
        background: none;
        font-weight: 500;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .image-tab.active {
        color: #0073E6;
        border-bottom: 2px solid #0073E6;
        margin-bottom: -2px;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    /* Table Context Menu */
    .table-context-menu {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none;
        min-width: 160px;
    }
    
    .context-menu-item {
        padding: 8px 15px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
    }
    
    .context-menu-item:hover {
        background-color: #f8f9fa;
    }
    
    .context-menu-separator {
        height: 1px;
        background-color: #dee2e6;
        margin: 5px 0;
    }
    
    /* Enhanced table selection */
    .table-selected {
        outline: 2px solid #0073E6 !important;
        background-color: rgba(0, 115, 230, 0.1) !important;
    }
    
    /* Form select small styling */
    .form-select-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        min-width: 80px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const visualEditor = document.getElementById('visual-editor');
    const sourceEditor = document.getElementById('source-editor');
    const htmlSource = document.getElementById('html-source');
    const bodyTextarea = document.getElementById('body');
    const toggleButton = document.getElementById('toggleSource');
    let isSourceMode = false;
    
    // Default professional email template
    const defaultTemplate = 
        '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">' +
            '<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">' +
                '<div style="text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0073E6; padding-bottom: 20px;">' +
                    '<h1 style="color: #0073E6; margin: 0; font-size: 28px;">{!!'{{'!!}hospital_name{!!'}}'!!}</h1>' +
                    '<p style="color: #6c757d; margin: 5px 0 0 0; font-size: 16px;">Professional Healthcare Services</p>' +
                '</div>' +
                '<h2 style="color: #0073E6; margin-bottom: 20px;">Dear {!!'{{'!!}patient_name{!!'}}'!!},</h2>' +
                '<p style="font-size: 16px; line-height: 1.6; color: #333;">We are pleased to confirm your upcoming appointment at <strong>{!!'{{'!!}hospital_name{!!'}}'!!}</strong>.</p>' +
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">' +
                    '<h3 style="color: #0073E6; margin-top: 0; margin-bottom: 15px;">Appointment Details</h3>' +
                    '<table style="width: 100%; border-collapse: collapse;">' +
                        '<tr>' +
                            '<td style="padding: 8px 0; font-weight: bold; color: #495057; width: 30%;">Doctor:</td>' +
                            '<td style="padding: 8px 0; color: #333;">{!!'{{'!!}doctor_name{!!'}}'!!}</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td style="padding: 8px 0; font-weight: bold; color: #495057;">Date:</td>' +
                            '<td style="padding: 8px 0; color: #333;">{!!'{{'!!}appointment_date{!!'}}'!!}</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td style="padding: 8px 0; font-weight: bold; color: #495057;">Time:</td>' +
                            '<td style="padding: 8px 0; color: #333;">{!!'{{'!!}appointment_time{!!'}}'!!}</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td style="padding: 8px 0; font-weight: bold; color: #495057;">Department:</td>' +
                            '<td style="padding: 8px 0; color: #333;">{!!'{{'!!}department{!!'}}'!!}</td>' +
                        '</tr>' +
                    '</table>' +
                '</div>' +
                '<div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 4px;">' +
                    '<p style="margin: 0; font-weight: bold; color: #856404;">Important Reminder:</p>' +
                    '<p style="margin: 8px 0 0 0; color: #856404;">Please arrive 15 minutes early and bring your ID and insurance card.</p>' +
                '</div>' +
                '<p style="font-size: 16px; line-height: 1.6; color: #333;">For any questions or to reschedule, please contact us:</p>' +
                '<div style="background: #0073E6; color: white; padding: 20px; border-radius: 6px; text-align: center; margin: 20px 0;">' +
                    '<p style="margin: 0; font-size: 18px; font-weight: bold;">Contact Information</p>' +
                    '<p style="margin: 8px 0 0 0;">Phone: {!!'{{'!!}hospital_phone{!!'}}'!!} | Address: {!!'{{'!!}hospital_address{!!'}}'!!}</p>' +
                '</div>' +
                '<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">' +
                    '<p style="color: #6c757d; font-size: 14px; margin: 0;">&copy; 2024 {!!'{{'!!}hospital_name{!!'}}'!!}. All rights reserved.</p>' +
                    '<p style="color: #6c757d; font-size: 14px; margin: 5px 0 0 0;">This is an automated message. Please do not reply directly to this email.</p>' +
                '</div>' +
            '</div>' +
        '</div>';
    
    // Initialize content
    function initializeContent() {
        let content = bodyTextarea.value.trim();
        if (!content) {
            content = defaultTemplate;
            bodyTextarea.value = content;
        }
        
        visualEditor.innerHTML = content;
        htmlSource.value = content;
    }
    
    // Rich text editor commands
    window.execCmd = function(command) {
        document.execCommand(command, false, null);
        updateContent();
        visualEditor.focus();
    }
    
    // Insert table function
    window.insertTable = function() {
        const tableHTML = `
            <table style="width: 100%; border-collapse: collapse; margin: 10px 0; border: 1px solid #ddd;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: bold;">Header 1</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: bold;">Header 2</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: bold;">Header 3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 12px;">Cell 1</td>
                        <td style="border: 1px solid #ddd; padding: 12px;">Cell 2</td>
                        <td style="border: 1px solid #ddd; padding: 12px;">Cell 3</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 12px;">Cell 4</td>
                        <td style="border: 1px solid #ddd; padding: 12px;">Cell 5</td>
                        <td style="border: 1px solid #ddd; padding: 12px;">Cell 6</td>
                    </tr>
                </tbody>
            </table>
            <p><br></p>
        `;
        
        document.execCommand('insertHTML', false, tableHTML);
        updateContent();
    }
    
    // Update content from visual editor
    function updateContent() {
        if (!isSourceMode) {
            bodyTextarea.value = visualEditor.innerHTML;
            htmlSource.value = visualEditor.innerHTML;
        }
    }
    
    // Toggle between visual and source mode
    toggleButton.addEventListener('click', function() {
        if (isSourceMode) {
            // Switch to visual mode
            visualEditor.innerHTML = htmlSource.value;
            bodyTextarea.value = htmlSource.value;
            
            document.getElementById('visual-editor').style.display = 'block';
            sourceEditor.style.display = 'none';
            
            this.innerHTML = '<i class="fas fa-code"></i> HTML';
            isSourceMode = false;
        } else {
            // Switch to source mode
            htmlSource.value = visualEditor.innerHTML;
            bodyTextarea.value = visualEditor.innerHTML;
            
            document.getElementById('visual-editor').style.display = 'none';
            sourceEditor.style.display = 'block';
            
            this.innerHTML = '<i class="fas fa-eye"></i> Visual';
            isSourceMode = true;
        }
    });
    
    // Update when typing in source editor
    htmlSource.addEventListener('input', function() {
        if (isSourceMode) {
            bodyTextarea.value = this.value;
        }
    });
    
    // Update when content changes in visual editor
    visualEditor.addEventListener('input', updateContent);
    visualEditor.addEventListener('keyup', updateContent);
    visualEditor.addEventListener('mouseup', updateContent);
    
    // Variable insertion functionality
    function setupVariableInsertion() {
        document.querySelectorAll('.variable-item').forEach(item => {
            // Skip template examples that don't have template variables
            if (item.classList.contains('template-example')) {
                return;
            }
            
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const codeElement = this.querySelector('code');
                if (!codeElement) return;
                
                const variable = codeElement.textContent.trim();
                
                if (isSourceMode) {
                    // Insert into HTML source editor
                    const cursorPos = htmlSource.selectionStart || htmlSource.value.length;
                    const textBefore = htmlSource.value.substring(0, cursorPos);
                    const textAfter = htmlSource.value.substring(cursorPos);
                    
                    htmlSource.value = textBefore + variable + textAfter;
                    bodyTextarea.value = htmlSource.value;
                    htmlSource.focus();
                    htmlSource.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);
                } else {
                    // Insert into visual editor
                    visualEditor.focus();
                    
                    try {
                        const selection = window.getSelection();
                        let range;
                        
                        if (selection.rangeCount > 0) {
                            range = selection.getRangeAt(0);
                        } else {
                            // Create range at end of editor if no selection
                            range = document.createRange();
                            range.selectNodeContents(visualEditor);
                            range.collapse(false);
                        }
                        
                        // Make sure we're inserting into the visual editor
                        if (!visualEditor.contains(range.commonAncestorContainer) && range.commonAncestorContainer !== visualEditor) {
                            range = document.createRange();
                            range.selectNodeContents(visualEditor);
                            range.collapse(false);
                        }
                        
                        const textNode = document.createTextNode(variable);
                        range.deleteContents();
                        range.insertNode(textNode);
                        
                        // Move cursor after inserted text
                        range.setStartAfter(textNode);
                        range.setEndAfter(textNode);
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                        updateContent();
                        
                    } catch (error) {
                        // Fallback: just append to the end
                        const textNode = document.createTextNode(' ' + variable + ' ');
                        visualEditor.appendChild(textNode);
                        updateContent();
                    }
                }
            });
        });
    }
    
    // Form submission handler
    document.getElementById('createEmailTemplateForm').addEventListener('submit', function(e) {
        if (isSourceMode) {
            bodyTextarea.value = htmlSource.value;
        } else {
            bodyTextarea.value = visualEditor.innerHTML;
        }
    });
    
    // Initialize
    initializeContent();
    
    // Setup variable insertion after DOM is ready
    setTimeout(setupVariableInsertion, 100);
    
    // Make tables properly editable
    visualEditor.addEventListener('click', function(e) {
        if (e.target.tagName === 'TD' || e.target.tagName === 'TH') {
            e.target.contentEditable = true;
            e.target.focus();
        }
    });
    
    // Enhanced Editor Functions
    
    // Font size function
    window.changeFontSize = function(size) {
        document.execCommand('fontSize', false, size);
        updateContent();
        visualEditor.focus();
    }
    
    // Text color function
    window.changeTextColor = function(color) {
        document.execCommand('foreColor', false, color);
        document.getElementById('text-color-icon').style.color = color;
        updateContent();
        visualEditor.focus();
    }
    
    // Background color function
    window.changeBackgroundColor = function(color) {
        document.execCommand('hiliteColor', false, color);
        document.getElementById('bg-color-icon').style.color = color;
        updateContent();
        visualEditor.focus();
    }
    
    // Image dialog function
    window.showImageDialog = function() {
        // First, ensure the visual editor is focused and store the current selection
        visualEditor.focus();
        
        // Store the current selection/cursor position
        const selection = window.getSelection();
        let storedRange = null;
        
        if (selection.rangeCount > 0) {
            storedRange = selection.getRangeAt(0).cloneRange();
        } else {
            // If no selection, create one at the beginning of the editor
            const range = document.createRange();
            if (visualEditor.firstChild) {
                range.setStart(visualEditor.firstChild, 0);
                range.setEnd(visualEditor.firstChild, 0);
            } else {
                range.setStart(visualEditor, 0);
                range.setEnd(visualEditor, 0);
            }
            storedRange = range;
        }
        
        // Store the range globally so we can use it later
        window.storedImageInsertionRange = storedRange;
        
        // Create modal if it doesn't exist
        let modal = document.getElementById('image-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'image-modal';
            modal.className = 'image-modal';
            modal.innerHTML = `
                <div class="image-modal-content">
                    <span class="modal-close">&times;</span>
                    <h3>Insert Image</h3>
                    <div class="image-tabs">
                        <button type="button" class="image-tab active" data-tab="upload">Upload</button>
                        <button type="button" class="image-tab" data-tab="url">URL</button>
                    </div>
                    <div id="upload-tab" class="tab-content active">
                        <div class="form-group mb-3">
                            <label for="image-upload" class="form-label">Select Image File:</label>
                            <input type="file" id="image-upload" class="form-control" accept="image/*">
                            <small class="text-muted">Supported formats: JPEG, PNG, GIF, WebP</small>
                        </div>
                    </div>
                    <div id="url-tab" class="tab-content">
                        <div class="form-group mb-3">
                            <label for="image-url" class="form-label">Image URL:</label>
                            <input type="url" id="image-url" class="form-control" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="image-alt" class="form-label">Alt Text:</label>
                        <input type="text" id="image-alt" class="form-control" placeholder="Image description">
                    </div>
                    <div class="form-group mb-3">
                        <label for="image-width" class="form-label">Width (optional):</label>
                        <input type="number" id="image-width" class="form-control" placeholder="300" min="50" max="800">
                        <small class="text-muted">Leave blank for original size</small>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" onclick="closeImageDialog()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="insertImage()">Insert Image</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Add event listeners
            modal.querySelector('.modal-close').addEventListener('click', function() {
                closeImageDialog();
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeImageDialog();
                }
            });
            
            // Tab switching
            modal.querySelectorAll('.image-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.dataset.tab;
                    modal.querySelectorAll('.image-tab').forEach(t => t.classList.remove('active'));
                    modal.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    modal.querySelector(`#${targetTab}-tab`).classList.add('active');
                });
            });
        }
        
        // Reset form
        modal.querySelector('#image-upload').value = '';
        modal.querySelector('#image-url').value = '';
        modal.querySelector('#image-alt').value = '';
        modal.querySelector('#image-width').value = '';
        
        // Show upload tab by default
        modal.querySelectorAll('.image-tab').forEach(t => t.classList.remove('active'));
        modal.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        modal.querySelector('.image-tab[data-tab="upload"]').classList.add('active');
        modal.querySelector('#upload-tab').classList.add('active');
        
        modal.style.display = 'block';
    }
    
    window.closeImageDialog = function() {
        const modal = document.getElementById('image-modal');
        if (modal) {
            modal.style.display = 'none';
            // Clear inputs
            modal.querySelector('#image-upload').value = '';
            modal.querySelector('#image-url').value = '';
            modal.querySelector('#image-alt').value = '';
            modal.querySelector('#image-width').value = '';
        }
    }
    
    window.insertImage = function() {
        const modal = document.getElementById('image-modal');
        if (!modal) {
            alert('Error: Modal not found');
            return;
        }
        
        const activeTabElement = modal.querySelector('.image-tab.active');
        if (!activeTabElement) {
            alert('Error: Active tab not found');
            return;
        }
        
        const activeTab = activeTabElement.dataset.tab;
        
        const altInput = modal.querySelector('#image-alt');
        const widthInput = modal.querySelector('#image-width');
        const altText = altInput ? (altInput.value || 'Image') : 'Image';
        const width = widthInput ? widthInput.value : '';
        
        if (activeTab === 'upload') {
            const fileInput = modal.querySelector('#image-upload');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    insertImageHtml(e.target.result, altText, width);
                    closeImageDialog();
                };
                reader.readAsDataURL(fileInput.files[0]);
                return;
            } else {
                alert('Please select an image file.');
                return;
            }
        } else if (activeTab === 'url') {
            const urlInput = modal.querySelector('#image-url');
            if (!urlInput) {
                alert('Error: URL input not found');
                return;
            }
            
            const imageUrl = urlInput.value.trim();
            
            if (!imageUrl) {
                alert('Please enter an image URL.');
                return;
            }
            
            // Validate URL format
            try {
                new URL(imageUrl);
            } catch (e) {
                alert('Please enter a valid URL.');
                return;
            }
            
            insertImageHtml(imageUrl, altText, width);
            
            closeImageDialog();
        } else {
            alert('Error: Unknown tab type');
        }
    }
    
    function insertImageHtml(src, alt, width) {
        // Create image element
        const img = document.createElement('img');
        img.src = src;
        img.alt = alt;
        img.style.maxWidth = '100%';
        img.style.height = 'auto';
        if (width) {
            img.style.width = width + 'px';
        }
        
        // Create line break
        const br = document.createElement('br');
        
        // Try to use the stored range first
        let inserted = false;
        
        if (window.storedImageInsertionRange) {
            try {
                const range = window.storedImageInsertionRange;
                
                // Make sure we're inserting into the visual editor
                if (visualEditor.contains(range.commonAncestorContainer) || range.commonAncestorContainer === visualEditor) {
                    // Clear any existing selection
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    
                    range.deleteContents();
                    range.insertNode(br);
                    range.insertNode(img);
                    
                    // Move cursor after the inserted content
                    range.setStartAfter(br);
                    range.setEndAfter(br);
                    selection.removeAllRanges();
                    selection.addRange(range);
                    
                    inserted = true;
                    
                    // Clear the stored range
                    window.storedImageInsertionRange = null;
                }
            } catch (rangeError) {
                // Range insertion failed, will use fallback
            }
        } else {
            // Fallback to current selection if no stored range
            const selection = window.getSelection();
            
            if (selection.rangeCount > 0) {
                try {
                    const range = selection.getRangeAt(0);
                    
                    // Make sure we're inserting into the visual editor
                    if (visualEditor.contains(range.commonAncestorContainer) || range.commonAncestorContainer === visualEditor) {
                        range.deleteContents();
                        range.insertNode(br);
                        range.insertNode(img);
                        
                        // Move cursor after the inserted content
                        range.setStartAfter(br);
                        range.setEndAfter(br);
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                        inserted = true;
                    }
                } catch (selectionError) {
                    // Selection insertion failed, will use fallback
                }
            }
        }
        
        if (!inserted) {
            // Better fallback: insert at the beginning or a more visible location
            if (visualEditor.children.length > 0) {
                // Insert after the first element for better visibility
                const firstElement = visualEditor.children[0];
                firstElement.insertAdjacentElement('afterend', img);
                img.insertAdjacentElement('afterend', br);
            } else {
                // If editor is empty, append normally
                visualEditor.appendChild(img);
                visualEditor.appendChild(br);
            }
            
            // Scroll the image into view
            setTimeout(() => {
                img.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 200);
        }
        
        updateContent();
        
        // Add a small delay before focusing to ensure the content is updated
        setTimeout(() => {
            visualEditor.focus();
        }, 100);
    }
    
    // Enhanced table functionality
    let selectedTable = null;
    let contextMenu = null;
    
    // Create context menu
    function createContextMenu() {
        if (contextMenu) return contextMenu;
        
        contextMenu = document.createElement('div');
        contextMenu.className = 'table-context-menu';
        contextMenu.innerHTML = `
            <div class="context-menu-item" onclick="addTableRow()">Add Row</div>
            <div class="context-menu-item" onclick="addTableColumn()">Add Column</div>
            <div class="context-menu-separator"></div>
            <div class="context-menu-item" onclick="deleteTableRow()">Delete Row</div>
            <div class="context-menu-item" onclick="deleteTableColumn()">Delete Column</div>
            <div class="context-menu-separator"></div>
            <div class="context-menu-item" onclick="changeTableBgColor()">Table Background Color</div>
            <div class="context-menu-item" onclick="deleteTable()">Delete Table</div>
        `;
        document.body.appendChild(contextMenu);
        
        // Hide menu when clicking elsewhere
        document.addEventListener('click', function() {
            contextMenu.style.display = 'none';
        });
        
        return contextMenu;
    }
    
    // Right-click on tables
    visualEditor.addEventListener('contextmenu', function(e) {
        if (e.target.closest('table')) {
            e.preventDefault();
            selectedTable = e.target.closest('table');
            const menu = createContextMenu();
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
        }
    });
    
    // Table manipulation functions
    window.addTableRow = function() {
        if (!selectedTable) return;
        
        const rows = selectedTable.querySelectorAll('tr');
        const lastRow = rows[rows.length - 1];
        const newRow = lastRow.cloneNode(true);
        
        // Clear content of new row cells
        newRow.querySelectorAll('td, th').forEach(cell => {
            cell.textContent = 'New Cell';
        });
        
        lastRow.parentNode.appendChild(newRow);
        updateContent();
        contextMenu.style.display = 'none';
    }
    
    window.addTableColumn = function() {
        if (!selectedTable) return;
        
        const rows = selectedTable.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const cell = document.createElement(index === 0 ? 'th' : 'td');
            cell.textContent = index === 0 ? 'New Header' : 'New Cell';
            cell.style.border = '1px solid #ddd';
            cell.style.padding = '12px';
            if (index === 0) {
                cell.style.backgroundColor = '#f8f9fa';
                cell.style.fontWeight = 'bold';
                cell.style.textAlign = 'center';
            }
            row.appendChild(cell);
        });
        
        updateContent();
        contextMenu.style.display = 'none';
    }
    
    window.deleteTableRow = function() {
        if (!selectedTable) return;
        
        const rows = selectedTable.querySelectorAll('tr');
        if (rows.length > 1) {
            rows[rows.length - 1].remove();
            updateContent();
        }
        contextMenu.style.display = 'none';
    }
    
    window.deleteTableColumn = function() {
        if (!selectedTable) return;
        
        const rows = selectedTable.querySelectorAll('tr');
        const firstRowCells = rows[0].querySelectorAll('td, th');
        
        if (firstRowCells.length > 1) {
            rows.forEach(row => {
                const cells = row.querySelectorAll('td, th');
                if (cells.length > 0) {
                    cells[cells.length - 1].remove();
                }
            });
            updateContent();
        }
        contextMenu.style.display = 'none';
    }
    
    window.changeTableBgColor = function() {
        if (!selectedTable) return;
        
        const color = prompt('Enter background color (hex code or name):', '#f8f9fa');
        if (color) {
            selectedTable.style.backgroundColor = color;
            updateContent();
        }
        contextMenu.style.display = 'none';
    }
    
    window.deleteTable = function() {
        if (!selectedTable && confirm('Are you sure you want to delete this table?')) {
            selectedTable.remove();
            updateContent();
        }
        contextMenu.style.display = 'none';
    }
});
</script>

<!-- Image Modal will be created dynamically -->
<div id="image-modal-placeholder"></div>
@endpush
