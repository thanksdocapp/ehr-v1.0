@extends('admin.layouts.app')

@section('title', 'Appearance Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Appearance</li>
@endsection

@push('styles')
@include('admin.shared.styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-palette me-2 text-primary"></i>Appearance Settings</h1>
        <p class="page-subtitle text-muted">Customize your application's theme, colors, and visual appearance</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <div class="stat-value">{{ ucfirst($statistics['current_theme'] ?? 'Default') }}</div>
                <div class="stat-label">Current Theme</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-palette"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['custom_colors'] ?? 0) }}</div>
                <div class="stat-label">Custom Colors</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['active_sections'] ?? 0) }}</div>
                <div class="stat-label">Active Sections</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ $statistics['last_updated'] ?? 'Never' }}</div>
                <div class="stat-label">Last Updated</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ contextRoute('settings.appearance.update') }}" id="appearanceForm">
        @csrf

        <!-- Theme Mode Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-moon me-2"></i>Theme Mode</h4>
                <small class="opacity-75">Select the default theme mode for your application</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="theme_mode" class="form-label">
                                <i class="fas fa-adjust me-1"></i>Theme Mode *
                            </label>
                            <select class="form-select" id="theme_mode" name="theme_mode" required>
                                <option value="default" {{ old('theme_mode', $settings['theme_mode'] ?? 'default') == 'default' ? 'selected' : '' }}>Default</option>
                                <option value="light" {{ old('theme_mode', $settings['theme_mode'] ?? 'default') == 'light' ? 'selected' : '' }}>Light</option>
                                <option value="dark" {{ old('theme_mode', $settings['theme_mode'] ?? 'default') == 'dark' ? 'selected' : '' }}>Dark</option>
                                <option value="custom" {{ old('theme_mode', $settings['theme_mode'] ?? 'default') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            <div class="form-help">Default theme mode applied to the application</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Color Settings Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-palette me-2"></i>Primary & Secondary Colors</h4>
                <small class="opacity-75">Customize your application's main color scheme</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="primary_color" class="form-label">
                                <i class="fas fa-paint-brush me-1"></i>Primary Color *
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color"
                                       value="{{ old('primary_color', $settings['primary_color'] ?? '#007bff') }}" required>
                                <input type="text" class="form-control" id="primary_color_hex"
                                       value="{{ old('primary_color', $settings['primary_color'] ?? '#007bff') }}"
                                       placeholder="#007bff" maxlength="7">
                            </div>
                            <div class="form-help">Main color used for buttons, links, and highlights</div>
                        </div>

                        <div class="form-group">
                            <label for="secondary_color" class="form-label">
                                <i class="fas fa-brush me-1"></i>Secondary Color *
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color"
                                       value="{{ old('secondary_color', $settings['secondary_color'] ?? '#6c757d') }}" required>
                                <input type="text" class="form-control" id="secondary_color_hex"
                                       value="{{ old('secondary_color', $settings['secondary_color'] ?? '#6c757d') }}"
                                       placeholder="#6c757d" maxlength="7">
                            </div>
                            <div class="form-help">Used for secondary buttons and muted elements</div>
                        </div>

                        <div class="form-group">
                            <label for="accent_color" class="form-label">
                                <i class="fas fa-palette me-1"></i>Accent Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color"
                                       value="{{ old('accent_color', $settings['accent_color'] ?? '#FF6B35') }}">
                                <input type="text" class="form-control" id="accent_color_hex"
                                       value="{{ old('accent_color', $settings['accent_color'] ?? '#FF6B35') }}"
                                       placeholder="#FF6B35" maxlength="7">
                            </div>
                            <div class="form-help">Highlight color for important callouts</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="text_color" class="form-label">
                                <i class="fas fa-font me-1"></i>Text Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="text_color" name="text_color"
                                       value="{{ old('text_color', $settings['text_color'] ?? '#2C3E50') }}">
                                <input type="text" class="form-control" id="text_color_hex"
                                       value="{{ old('text_color', $settings['text_color'] ?? '#2C3E50') }}"
                                       placeholder="#2C3E50" maxlength="7">
                            </div>
                            <div class="form-help">Default text color for content</div>
                        </div>

                        <div class="form-group">
                            <label for="background_color" class="form-label">
                                <i class="fas fa-square me-1"></i>Background Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="background_color" name="background_color"
                                       value="{{ old('background_color', $settings['background_color'] ?? '#FFFFFF') }}">
                                <input type="text" class="form-control" id="background_color_hex"
                                       value="{{ old('background_color', $settings['background_color'] ?? '#FFFFFF') }}"
                                       placeholder="#FFFFFF" maxlength="7">
                            </div>
                            <div class="form-help">Default background color</div>
                        </div>

                        <div class="form-group">
                            <label for="sidebar_color" class="form-label">
                                <i class="fas fa-columns me-1"></i>Sidebar Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="sidebar_color" name="sidebar_color"
                                       value="{{ old('sidebar_color', $settings['sidebar_color'] ?? '#2C3E50') }}">
                                <input type="text" class="form-control" id="sidebar_color_hex"
                                       value="{{ old('sidebar_color', $settings['sidebar_color'] ?? '#2C3E50') }}"
                                       placeholder="#2C3E50" maxlength="7">
                            </div>
                            <div class="form-help">Admin sidebar background color</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Colors Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-hand-pointer me-2"></i>Button Colors</h4>
                <small class="opacity-75">Customize button colors for different states</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="success_color" class="form-label">
                                <i class="fas fa-check-circle me-1 text-success"></i>Success Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="success_color" name="success_color"
                                       value="{{ old('success_color', $settings['success_color'] ?? '#28A745') }}">
                                <input type="text" class="form-control" id="success_color_hex"
                                       value="{{ old('success_color', $settings['success_color'] ?? '#28A745') }}"
                                       placeholder="#28A745" maxlength="7">
                            </div>
                            <div class="form-help">Confirm, Save, Submit buttons</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="danger_color" class="form-label">
                                <i class="fas fa-exclamation-circle me-1 text-danger"></i>Danger Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="danger_color" name="danger_color"
                                       value="{{ old('danger_color', $settings['danger_color'] ?? '#DC3545') }}">
                                <input type="text" class="form-control" id="danger_color_hex"
                                       value="{{ old('danger_color', $settings['danger_color'] ?? '#DC3545') }}"
                                       placeholder="#DC3545" maxlength="7">
                            </div>
                            <div class="form-help">Delete, Cancel, Error buttons</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="warning_color" class="form-label">
                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>Warning Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="warning_color" name="warning_color"
                                       value="{{ old('warning_color', $settings['warning_color'] ?? '#FFC107') }}">
                                <input type="text" class="form-control" id="warning_color_hex"
                                       value="{{ old('warning_color', $settings['warning_color'] ?? '#FFC107') }}"
                                       placeholder="#FFC107" maxlength="7">
                            </div>
                            <div class="form-help">Caution, Pending buttons</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="info_color" class="form-label">
                                <i class="fas fa-info-circle me-1 text-info"></i>Info Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="info_color" name="info_color"
                                       value="{{ old('info_color', $settings['info_color'] ?? '#17A2B8') }}">
                                <input type="text" class="form-control" id="info_color_hex"
                                       value="{{ old('info_color', $settings['info_color'] ?? '#17A2B8') }}"
                                       placeholder="#17A2B8" maxlength="7">
                            </div>
                            <div class="form-help">Info, Details, Help buttons</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Styling Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-arrows-alt-v me-2"></i>Button Styling</h4>
                <small class="opacity-75">Customize button dimensions and hover effects</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_height" class="form-label">
                                <i class="fas fa-arrows-alt-v me-1"></i>Button Height (px)
                            </label>
                            <input type="number" class="form-control" id="button_height" name="button_height"
                                   value="{{ old('button_height', $settings['button_height'] ?? 38) }}"
                                   min="28" max="60" placeholder="38">
                            <div class="form-help">Default button height (28-60px)</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_hover_color" class="form-label">
                                <i class="fas fa-mouse-pointer me-1"></i>Primary Hover Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="button_hover_color" name="button_hover_color"
                                       value="{{ old('button_hover_color', $settings['button_hover_color'] ?? '#0056b3') }}">
                                <input type="text" class="form-control" id="button_hover_color_hex"
                                       value="{{ old('button_hover_color', $settings['button_hover_color'] ?? '#0056b3') }}"
                                       placeholder="#0056b3" maxlength="7">
                            </div>
                            <div class="form-help">Primary button hover state color</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_secondary_hover_color" class="form-label">
                                <i class="fas fa-mouse-pointer me-1"></i>Secondary Hover Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="button_secondary_hover_color" name="button_secondary_hover_color"
                                       value="{{ old('button_secondary_hover_color', $settings['button_secondary_hover_color'] ?? '#545b62') }}">
                                <input type="text" class="form-control" id="button_secondary_hover_color_hex"
                                       value="{{ old('button_secondary_hover_color', $settings['button_secondary_hover_color'] ?? '#545b62') }}"
                                       placeholder="#545b62" maxlength="7">
                            </div>
                            <div class="form-help">Secondary button hover state color</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_success_hover_color" class="form-label">
                                <i class="fas fa-mouse-pointer me-1 text-success"></i>Success Hover Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="button_success_hover_color" name="button_success_hover_color"
                                       value="{{ old('button_success_hover_color', $settings['button_success_hover_color'] ?? '#1e7e34') }}">
                                <input type="text" class="form-control" id="button_success_hover_color_hex"
                                       value="{{ old('button_success_hover_color', $settings['button_success_hover_color'] ?? '#1e7e34') }}"
                                       placeholder="#1e7e34" maxlength="7">
                            </div>
                            <div class="form-help">Success button hover state</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_danger_hover_color" class="form-label">
                                <i class="fas fa-mouse-pointer me-1 text-danger"></i>Danger Hover Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="button_danger_hover_color" name="button_danger_hover_color"
                                       value="{{ old('button_danger_hover_color', $settings['button_danger_hover_color'] ?? '#c82333') }}">
                                <input type="text" class="form-control" id="button_danger_hover_color_hex"
                                       value="{{ old('button_danger_hover_color', $settings['button_danger_hover_color'] ?? '#c82333') }}"
                                       placeholder="#c82333" maxlength="7">
                            </div>
                            <div class="form-help">Danger button hover state</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_warning_hover_color" class="form-label">
                                <i class="fas fa-mouse-pointer me-1 text-warning"></i>Warning Hover Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="button_warning_hover_color" name="button_warning_hover_color"
                                       value="{{ old('button_warning_hover_color', $settings['button_warning_hover_color'] ?? '#d39e00') }}">
                                <input type="text" class="form-control" id="button_warning_hover_color_hex"
                                       value="{{ old('button_warning_hover_color', $settings['button_warning_hover_color'] ?? '#d39e00') }}"
                                       placeholder="#d39e00" maxlength="7">
                            </div>
                            <div class="form-help">Warning button hover state</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_info_hover_color" class="form-label">
                                <i class="fas fa-mouse-pointer me-1 text-info"></i>Info Hover Color
                            </label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="button_info_hover_color" name="button_info_hover_color"
                                       value="{{ old('button_info_hover_color', $settings['button_info_hover_color'] ?? '#138496') }}">
                                <input type="text" class="form-control" id="button_info_hover_color_hex"
                                       value="{{ old('button_info_hover_color', $settings['button_info_hover_color'] ?? '#138496') }}"
                                       placeholder="#138496" maxlength="7">
                            </div>
                            <div class="form-help">Info button hover state</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Live Preview</h4>
                <small class="opacity-75">See how your color choices will look throughout the application</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="border rounded p-4" id="colorPreview" style="background-color: {{ old('background_color', $settings['background_color'] ?? '#FFFFFF') }};">
                            <div style="color: {{ old('text_color', $settings['text_color'] ?? '#2C3E50') }};">
                                <h5 class="mb-3">Sample Content Preview</h5>
                                <p class="mb-3">This is how your colors will appear together throughout the application.</p>

                                <!-- Button Preview -->
                                <div class="mb-4">
                                    <h6 class="mb-2">Buttons</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn preview-btn-primary">Primary</button>
                                        <button type="button" class="btn preview-btn-secondary">Secondary</button>
                                        <button type="button" class="btn preview-btn-success">Success</button>
                                        <button type="button" class="btn preview-btn-danger">Danger</button>
                                        <button type="button" class="btn preview-btn-warning">Warning</button>
                                        <button type="button" class="btn preview-btn-info">Info</button>
                                    </div>
                                </div>

                                <!-- Outline Button Preview -->
                                <div class="mb-4">
                                    <h6 class="mb-2">Outline Buttons</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn preview-btn-outline-primary">Primary</button>
                                        <button type="button" class="btn preview-btn-outline-secondary">Secondary</button>
                                        <button type="button" class="btn preview-btn-outline-success">Success</button>
                                        <button type="button" class="btn preview-btn-outline-danger">Danger</button>
                                    </div>
                                </div>

                                <!-- Badge Preview -->
                                <div class="mb-4">
                                    <h6 class="mb-2">Badges & Labels</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge preview-badge-primary">Primary</span>
                                        <span class="badge preview-badge-secondary">Secondary</span>
                                        <span class="badge preview-badge-success">Success</span>
                                        <span class="badge preview-badge-danger">Danger</span>
                                        <span class="badge preview-badge-warning text-dark">Warning</span>
                                        <span class="badge preview-badge-info">Info</span>
                                        <span class="badge preview-badge-accent">Accent</span>
                                    </div>
                                </div>

                                <!-- Link Preview -->
                                <div class="mb-3">
                                    <h6 class="mb-2">Links</h6>
                                    <a href="#" class="preview-link" onclick="return false;">This is a sample link</a>
                                </div>

                                <!-- Alert Preview -->
                                <div class="mb-0">
                                    <h6 class="mb-2">Alert Sample</h6>
                                    <div class="alert preview-alert-primary mb-0" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>This is a sample alert message with your primary color.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Typography & Styling Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-font me-2"></i>Typography & Styling</h4>
                <small class="opacity-75">Customize fonts and border styles</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="font_family" class="form-label">
                                <i class="fas fa-font me-1"></i>Font Family
                            </label>
                            <select class="form-select" id="font_family" name="font_family">
                                <option value="Lato" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                <option value="Roboto" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="Open Sans" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                <option value="Montserrat" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                                <option value="Poppins" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                <option value="Inter" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                <option value="Arial" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Arial' ? 'selected' : '' }}>Arial</option>
                                <option value="Helvetica" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                                <option value="Times New Roman" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                                <option value="Georgia" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                                <option value="Courier New" {{ old('font_family', $settings['font_family'] ?? 'Lato') == 'Courier New' ? 'selected' : '' }}>Courier New</option>
                            </select>
                            <div class="form-help">Default font family for the application</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="border_radius" class="form-label">
                                <i class="fas fa-square me-1"></i>Border Radius (px)
                            </label>
                            <input type="number" class="form-control" id="border_radius" name="border_radius"
                                   value="{{ old('border_radius', $settings['border_radius'] ?? 15) }}" 
                                   min="0" max="50" placeholder="15">
                            <div class="form-help">Default border radius for rounded corners (0-50px)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-section">
            <div class="form-section-body text-center">
                <button type="submit" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-save me-2"></i>Save Appearance Settings
                </button>
                <button type="button" class="btn btn-outline-warning btn-lg me-3" id="resetColors">
                    <i class="fas fa-undo me-2"></i>Reset to Defaults
                </button>
                <a href="{{ contextRoute('settings.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Sync color picker with hex input
    function syncColorInputs(colorPickerId, hexInputId) {
        $('#' + colorPickerId).on('input change', function() {
            $('#' + hexInputId).val($(this).val().toUpperCase());
            updateColorPreview();
        });

        $('#' + hexInputId).on('input change', function() {
            let hex = $(this).val().trim();
            if (!hex.startsWith('#')) {
                hex = '#' + hex;
            }
            if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
                $('#' + colorPickerId).val(hex);
                $(this).val(hex.toUpperCase());
                updateColorPreview();
            }
        });
    }

    // Initialize color sync for all color inputs
    const colorFields = [
        'primary_color', 'secondary_color', 'accent_color',
        'text_color', 'background_color', 'sidebar_color',
        'success_color', 'danger_color', 'warning_color', 'info_color',
        'button_hover_color', 'button_secondary_hover_color',
        'button_success_hover_color', 'button_danger_hover_color',
        'button_warning_hover_color', 'button_info_hover_color'
    ];

    colorFields.forEach(function(field) {
        syncColorInputs(field, field + '_hex');
    });

    // Calculate lighter/darker versions of a color
    function lightenColor(hex, percent) {
        hex = hex.replace('#', '');
        const r = Math.min(255, parseInt(hex.substr(0, 2), 16) + Math.round(2.55 * percent));
        const g = Math.min(255, parseInt(hex.substr(2, 2), 16) + Math.round(2.55 * percent));
        const b = Math.min(255, parseInt(hex.substr(4, 2), 16) + Math.round(2.55 * percent));
        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    function darkenColor(hex, percent) {
        hex = hex.replace('#', '');
        const r = Math.max(0, parseInt(hex.substr(0, 2), 16) - Math.round(2.55 * percent));
        const g = Math.max(0, parseInt(hex.substr(2, 2), 16) - Math.round(2.55 * percent));
        const b = Math.max(0, parseInt(hex.substr(4, 2), 16) - Math.round(2.55 * percent));
        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    // Update color preview with all elements
    function updateColorPreview() {
        const colors = {
            primary: $('#primary_color').val(),
            secondary: $('#secondary_color').val(),
            accent: $('#accent_color').val(),
            text: $('#text_color').val(),
            background: $('#background_color').val(),
            success: $('#success_color').val(),
            danger: $('#danger_color').val(),
            warning: $('#warning_color').val(),
            info: $('#info_color').val()
        };

        const preview = $('#colorPreview');

        // Background and text
        preview.css('background-color', colors.background);
        preview.find('> div').css('color', colors.text);
        preview.find('h5, h6').css('color', colors.text);

        // Solid buttons
        $('.preview-btn-primary').css({
            'background-color': colors.primary,
            'border-color': colors.primary,
            'color': '#fff'
        });
        $('.preview-btn-secondary').css({
            'background-color': colors.secondary,
            'border-color': colors.secondary,
            'color': '#fff'
        });
        $('.preview-btn-success').css({
            'background-color': colors.success,
            'border-color': colors.success,
            'color': '#fff'
        });
        $('.preview-btn-danger').css({
            'background-color': colors.danger,
            'border-color': colors.danger,
            'color': '#fff'
        });
        $('.preview-btn-warning').css({
            'background-color': colors.warning,
            'border-color': colors.warning,
            'color': '#212529'
        });
        $('.preview-btn-info').css({
            'background-color': colors.info,
            'border-color': colors.info,
            'color': '#fff'
        });

        // Outline buttons
        $('.preview-btn-outline-primary').css({
            'background-color': 'transparent',
            'border-color': colors.primary,
            'color': colors.primary
        });
        $('.preview-btn-outline-secondary').css({
            'background-color': 'transparent',
            'border-color': colors.secondary,
            'color': colors.secondary
        });
        $('.preview-btn-outline-success').css({
            'background-color': 'transparent',
            'border-color': colors.success,
            'color': colors.success
        });
        $('.preview-btn-outline-danger').css({
            'background-color': 'transparent',
            'border-color': colors.danger,
            'color': colors.danger
        });

        // Badges
        $('.preview-badge-primary').css('background-color', colors.primary);
        $('.preview-badge-secondary').css('background-color', colors.secondary);
        $('.preview-badge-success').css('background-color', colors.success);
        $('.preview-badge-danger').css('background-color', colors.danger);
        $('.preview-badge-warning').css('background-color', colors.warning);
        $('.preview-badge-info').css('background-color', colors.info);
        $('.preview-badge-accent').css('background-color', colors.accent);

        // Links
        $('.preview-link').css('color', colors.primary);

        // Alert
        $('.preview-alert-primary').css({
            'background-color': lightenColor(colors.primary, 40),
            'border-color': colors.primary,
            'color': darkenColor(colors.primary, 20)
        });
    }

    // Add hover effects for preview buttons
    $('.preview-btn-primary, .preview-btn-secondary, .preview-btn-success, .preview-btn-danger, .preview-btn-warning, .preview-btn-info').hover(
        function() {
            const currentBg = $(this).css('background-color');
            $(this).css('opacity', '0.85');
        },
        function() {
            $(this).css('opacity', '1');
        }
    );

    $('.preview-btn-outline-primary').hover(
        function() {
            $(this).css({
                'background-color': $('#primary_color').val(),
                'color': '#fff'
            });
        },
        function() {
            $(this).css({
                'background-color': 'transparent',
                'color': $('#primary_color').val()
            });
        }
    );

    $('.preview-btn-outline-secondary').hover(
        function() {
            $(this).css({
                'background-color': $('#secondary_color').val(),
                'color': '#fff'
            });
        },
        function() {
            $(this).css({
                'background-color': 'transparent',
                'color': $('#secondary_color').val()
            });
        }
    );

    $('.preview-btn-outline-success').hover(
        function() {
            $(this).css({
                'background-color': $('#success_color').val(),
                'color': '#fff'
            });
        },
        function() {
            $(this).css({
                'background-color': 'transparent',
                'color': $('#success_color').val()
            });
        }
    );

    $('.preview-btn-outline-danger').hover(
        function() {
            $(this).css({
                'background-color': $('#danger_color').val(),
                'color': '#fff'
            });
        },
        function() {
            $(this).css({
                'background-color': 'transparent',
                'color': $('#danger_color').val()
            });
        }
    );

    // Initialize preview on page load
    updateColorPreview();

    // Form submission validation
    $('#appearanceForm').on('submit', function(e) {
        // Validate hex color format for required fields
        const requiredColorInputs = ['primary_color', 'secondary_color'];
        let isValid = true;

        requiredColorInputs.forEach(function(inputId) {
            const colorValue = $('#' + inputId).val();
            if (!/^#[0-9A-Fa-f]{6}$/.test(colorValue) && !/^#[0-9A-Fa-f]{3}$/.test(colorValue)) {
                isValid = false;
                alert('Please enter a valid color for ' + inputId.replace(/_/g, ' '));
                $('#' + inputId).focus();
            }
        });

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        // Show loading message
        if (typeof showLoading === 'function') {
            showLoading('Saving appearance settings...');
        }
    });

    // Reset to default colors button
    $('#resetColors').on('click', function() {
        if (confirm('Reset all colors to default values?')) {
            $('#primary_color, #primary_color_hex').val('#007BFF');
            $('#secondary_color, #secondary_color_hex').val('#6C757D');
            $('#accent_color, #accent_color_hex').val('#FF6B35');
            $('#text_color, #text_color_hex').val('#2C3E50');
            $('#background_color, #background_color_hex').val('#FFFFFF');
            $('#sidebar_color, #sidebar_color_hex').val('#2C3E50');
            $('#success_color, #success_color_hex').val('#28A745');
            $('#danger_color, #danger_color_hex').val('#DC3545');
            $('#warning_color, #warning_color_hex').val('#FFC107');
            $('#info_color, #info_color_hex').val('#17A2B8');
            // Reset button styling
            $('#button_height').val('38');
            $('#button_hover_color, #button_hover_color_hex').val('#0056B3');
            $('#button_secondary_hover_color, #button_secondary_hover_color_hex').val('#545B62');
            $('#button_success_hover_color, #button_success_hover_color_hex').val('#1E7E34');
            $('#button_danger_hover_color, #button_danger_hover_color_hex').val('#C82333');
            $('#button_warning_hover_color, #button_warning_hover_color_hex').val('#D39E00');
            $('#button_info_hover_color, #button_info_hover_color_hex').val('#138496');
            updateColorPreview();
        }
    });
});
</script>
<style>
    /* Preview button styles */
    .preview-btn-primary, .preview-btn-secondary, .preview-btn-success,
    .preview-btn-danger, .preview-btn-warning, .preview-btn-info,
    .preview-btn-outline-primary, .preview-btn-outline-secondary,
    .preview-btn-outline-success, .preview-btn-outline-danger {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        border-width: 1px;
        border-style: solid;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .preview-badge-primary, .preview-badge-secondary, .preview-badge-success,
    .preview-badge-danger, .preview-badge-warning, .preview-badge-info,
    .preview-badge-accent {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        border-radius: 0.375rem;
        color: #fff;
    }

    .preview-link {
        text-decoration: underline;
        cursor: pointer;
    }

    .preview-link:hover {
        opacity: 0.8;
    }

    .preview-alert-primary {
        padding: 1rem;
        border-radius: 0.375rem;
        border-width: 1px;
        border-style: solid;
    }

    #colorPreview {
        transition: background-color 0.3s ease;
    }
</style>
@endpush

