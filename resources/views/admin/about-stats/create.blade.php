@extends('admin.layouts.app')

@section('title', 'Create About Statistic')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('about-stats.index') }}">About Statistics</a></li>
    <li class="breadcrumb-item active">Create Statistic</li>
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

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}

.color-preview {
    width: 40px;
    height: 40px;
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.color-preview:hover {
    border-color: #1cc88a;
    transform: scale(1.1);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-plus me-2 text-primary"></i>Create About Statistic</h1>
        <p class="page-subtitle text-muted">Add a new statistic to the About section</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createAboutStatForm" method="POST" action="{{ contextRoute('about-stats.store') }}">
                @csrf
                
                <!-- Statistic Content -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistic Content</h4>
                        <small class="opacity-75">Define the statistic content and appearance</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Title *
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Enter statistic title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="value" class="form-label">
                                <i class="fas fa-hashtag me-1"></i>Value *
                            </label>
                            <input type="text" class="form-control @error('value') is-invalid @enderror" 
                                   id="value" name="value" value="{{ old('value') }}" 
                                   placeholder="Enter statistic value" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="icon" class="form-label">
                                <i class="fas fa-icons me-1"></i>Font Awesome Icon
                            </label>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon', 'fas fa-star') }}" 
                                   placeholder="e.g., fas fa-heart">
                            <small class="form-text text-muted">Use Font Awesome classes (e.g., fas fa-heart).</small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prefix" class="form-label">
                                        <i class="fas fa-arrow-left me-1"></i>Prefix
                                    </label>
                                    <input type="text" class="form-control @error('prefix') is-invalid @enderror" 
                                           id="prefix" name="prefix" value="{{ old('prefix') }}" 
                                           placeholder="e.g., $, +, #" maxlength="10">
                                    <small class="form-text text-muted">Text to display before the value (optional).</small>
                                    @error('prefix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="suffix" class="form-label">
                                        <i class="fas fa-arrow-right me-1"></i>Suffix
                                    </label>
                                    <input type="text" class="form-control @error('suffix') is-invalid @enderror" 
                                           id="suffix" name="suffix" value="{{ old('suffix') }}" 
                                           placeholder="e.g., +, %, K, M" maxlength="10">
                                    <small class="form-text text-muted">Text to display after the value (optional).</small>
                                    @error('suffix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subtitle" class="form-label">
                                <i class="fas fa-text-height me-1"></i>Subtitle
                            </label>
                            <input type="text" class="form-control @error('subtitle') is-invalid @enderror" 
                                   id="subtitle" name="subtitle" value="{{ old('subtitle') }}" 
                                   placeholder="Enter subtitle (optional)" maxlength="255">
                            <small class="form-text text-muted">Optional subtitle to display below the main title.</small>
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Enter description (optional)" maxlength="1000">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Optional description to provide more context about this statistic.</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="color" class="form-label">
                                <i class="fas fa-palette me-1"></i>Color
                            </label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control-color me-3 @error('color') is-invalid @enderror" 
                                       id="color" name="color" value="{{ old('color', '#0d6efd') }}" 
                                       style="width: 60px; height: 40px;">
                                <input type="text" class="form-control" 
                                       id="color_text" value="{{ old('color', '#0d6efd') }}" 
                                       placeholder="#0d6efd" readonly>
                            </div>
                            <small class="form-text text-muted">Choose a color for the statistic icon and elements.</small>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Display Settings</h4>
                        <small class="opacity-75">Configure how this statistic appears</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order" class="form-label">
                                        <i class="fas fa-sort-numeric-down me-1"></i>Sort Order
                                    </label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                           min="0" placeholder="0">
                                    <small class="form-text text-muted">Lower numbers appear first. Use 0 for automatic ordering.</small>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-eye me-1"></i>Status
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active (visible on frontend)
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Toggle to show/hide this statistic on the website.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Statistic
                        </button>
                        <a href="{{ contextRoute('about-stats.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Live Preview Sidebar -->
        <div class="col-lg-4">
            <div class="form-section sticky-top" style="top: 2rem;">
                <div class="form-section-header">
                    <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Live Preview</h4>
                    <small class="opacity-75">See how your statistic will look</small>
                </div>
                <div class="form-section-body">
                    <div id="stat-preview" class="text-center p-4" style="border: 2px dashed #e3e6f0; border-radius: 12px; min-height: 200px; background: #f8f9fc;">
                        <div class="mb-3">
                            <i id="preview-icon" class="fas fa-star" style="font-size: 3rem; color: #0d6efd;"></i>
                        </div>
                        <div class="mb-2">
                            <h2 class="mb-0" style="color: #5a5c69;">
                                <span id="preview-prefix"></span>
                                <span id="preview-value">0</span>
                                <span id="preview-suffix"></span>
                            </h2>
                        </div>
                        <div class="mb-2">
                            <h5 id="preview-title" class="mb-0" style="color: #5a5c69;">Statistic Title</h5>
                        </div>
                        <div class="mb-2">
                            <small id="preview-subtitle" class="text-muted" style="display: none;"></small>
                        </div>
                        <div>
                            <p id="preview-description" class="text-muted mb-0" style="font-size: 0.9rem; display: none;"></p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="info-card">
                            <h6><i class="fas fa-lightbulb me-1"></i>Tips</h6>
                            <ul class="mb-0">
                                <li>Use meaningful titles that describe the statistic</li>
                                <li>Icons help users quickly understand the context</li>
                                <li>Prefixes and suffixes add context (e.g., $, +, %)</li>
                                <li>Keep descriptions concise and informative</li>
                                <li>Choose colors that align with your brand</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update live preview as user types
    $('#title, #value, #icon, #color, #prefix, #suffix, #subtitle, #description').on('input', function() {
        var title = $('#title').val();
        var value = $('#value').val();
        var icon = $('#icon').val();
        var color = $('#color').val();
        var prefix = $('#prefix').val();
        var suffix = $('#suffix').val();
        var subtitle = $('#subtitle').val();
        var description = $('#description').val();

        $('#preview-title').text(title || 'Statistic Title');
        $('#preview-value').text(value || '0');
        $('#preview-icon').attr('class', icon || 'fas fa-star').css('color', color);
        $('#preview-prefix').text(prefix).toggle(!!prefix);
        $('#preview-suffix').text(suffix).toggle(!!suffix);

        $('#preview-subtitle').text(subtitle).toggle(!!subtitle);
        $('#preview-description').text(description).toggle(!!description);
    });

    // Sync color picker with text input
    $('#color').on('change', function() {
        $('#color_text').val($(this).val());
    });

    // Form validation
    $('#createAboutStatForm').on('submit', function(e) {
        var title = $('#title').val().trim();
        var value = $('#value').val().trim();
        
        if (!title) {
            alert('Please enter a title for the statistic.');
            $('#title').focus();
            e.preventDefault();
            return false;
        }
        
        if (!value) {
            alert('Please enter a value for the statistic.');
            $('#value').focus();
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush

