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
                <h4 class="mb-0"><i class="fas fa-palette me-2"></i>Color Settings</h4>
                <small class="opacity-75">Customize your application's color scheme</small>
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
                            <div class="form-help">Main color used throughout the application</div>
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
                            <div class="form-help">Accent color for secondary elements</div>
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
                            <div class="form-help">Highlight color for important elements</div>
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

                        <!-- Color Preview -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-eye me-1"></i>Color Preview
                            </label>
                            <div class="border rounded p-3" id="colorPreview" style="min-height: 100px; background-color: {{ old('background_color', $settings['background_color'] ?? '#FFFFFF') }};">
                                <div style="color: {{ old('text_color', $settings['text_color'] ?? '#2C3E50') }};">
                                    <p class="mb-2"><strong>Sample Text</strong></p>
                                    <p class="mb-2 small">This is how your colors will appear together.</p>
                                    <div class="d-flex gap-2">
                                        <span class="badge" style="background-color: {{ old('primary_color', $settings['primary_color'] ?? '#007bff') }};">Primary</span>
                                        <span class="badge" style="background-color: {{ old('secondary_color', $settings['secondary_color'] ?? '#6c757d') }};">Secondary</span>
                                        <span class="badge" style="background-color: {{ old('accent_color', $settings['accent_color'] ?? '#FF6B35') }};">Accent</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-help">Live preview of your color scheme</div>
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
            if (hex.startsWith('#')) {
                hex = hex.substring(1);
            }
            if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
                $('#' + colorPickerId).val('#' + hex);
                updateColorPreview();
            }
        });
    }

    // Initialize color sync
    syncColorInputs('primary_color', 'primary_color_hex');
    syncColorInputs('secondary_color', 'secondary_color_hex');
    syncColorInputs('accent_color', 'accent_color_hex');
    syncColorInputs('text_color', 'text_color_hex');
    syncColorInputs('background_color', 'background_color_hex');

    // Update color preview
    function updateColorPreview() {
        const primaryColor = $('#primary_color').val();
        const secondaryColor = $('#secondary_color').val();
        const accentColor = $('#accent_color').val();
        const textColor = $('#text_color').val();
        const backgroundColor = $('#background_color').val();

        const preview = $('#colorPreview');
        preview.css('background-color', backgroundColor);
        preview.find('div').css('color', textColor);
        preview.find('.badge').eq(0).css('background-color', primaryColor);
        preview.find('.badge').eq(1).css('background-color', secondaryColor);
        preview.find('.badge').eq(2).css('background-color', accentColor);
    }

    // Initialize preview on page load
    updateColorPreview();

    // Form submission validation
    $('#appearanceForm').on('submit', function(e) {
        // Validate hex color format
        const colorInputs = ['primary_color', 'secondary_color'];
        let isValid = true;

        colorInputs.forEach(function(inputId) {
            const colorValue = $('#' + inputId).val();
            if (!/^#[0-9A-Fa-f]{6}$/.test(colorValue) && !/^#[0-9A-Fa-f]{3}$/.test(colorValue)) {
                isValid = false;
                alert('Please enter a valid color for ' + inputId.replace('_', ' '));
                $('#' + inputId).focus();
            }
        });

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        // Show loading message
        showLoading('Saving appearance settings...');
    });
});
</script>
@endpush

