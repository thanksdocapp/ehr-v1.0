@extends('admin.layouts.app')

@section('title', 'Create Homepage Feature')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('homepage-features.index') }}">Homepage Features</a></li>
    <li class="breadcrumb-item active">Create Feature</li>
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
        <h1><i class="fas fa-plus me-2 text-primary"></i>Create Homepage Feature</h1>
        <p class="page-subtitle text-muted">Add a new feature to your homepage</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createHomepageFeatureForm" method="POST" action="{{ contextRoute('homepage-features.store') }}">
                @csrf
                
                <!-- Feature Content Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Feature Content</h4>
                        <small class="opacity-75">Feature text content and messaging</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Title *
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Enter feature title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="subtitle" class="form-label">
                                <i class="fas fa-text-height me-1"></i>Subtitle
                            </label>
                            <input type="text" class="form-control @error('subtitle') is-invalid @enderror" 
                                   id="subtitle" name="subtitle" value="{{ old('subtitle') }}" 
                                   placeholder="Enter feature subtitle">
                            <div class="form-help">Brief subtitle or tagline for the feature</div>
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Description *
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Enter feature description" required>{{ old('description') }}</textarea>
                            <div class="form-help">Detailed description of the feature content</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Icon & Style Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-palette me-2"></i>Icon & Style</h4>
                        <small class="opacity-75">Feature icon and visual styling</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="icon" class="form-label">
                                        <i class="fas fa-icons me-1"></i>Font Awesome Icon *
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                               id="icon" name="icon" value="{{ old('icon', 'fas fa-star') }}" 
                                               placeholder="e.g., fas fa-clock" required>
                                        <button type="button" class="btn btn-outline-secondary" id="iconPreviewBtn">
                                            <i id="iconPreview" class="{{ old('icon', 'fas fa-star') }}"></i> Preview
                                        </button>
                                    </div>
                                    <div class="form-help">
                                        Use Font Awesome classes (e.g., fas fa-clock, fas fa-heart, fas fa-user-md).
                                        <a href="https://fontawesome.com/icons" target="_blank">Browse icons here</a>
                                    </div>
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color" class="form-label">
                                        <i class="fas fa-palette me-1"></i>Icon Color
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control color-preview @error('color') is-invalid @enderror" 
                                               id="color" name="color" value="{{ old('color', '#0d6efd') }}" 
                                               style="width: 60px; height: 40px; padding: 0;">
                                        <input type="text" class="form-control ms-2 @error('color') is-invalid @enderror" 
                                               id="color_text" value="{{ old('color', '#0d6efd') }}" 
                                               placeholder="#0d6efd" readonly>
                                    </div>
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Display Settings</h4>
                        <small class="opacity-75">Feature appearance and ordering</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order" class="form-label">
                                        <i class="fas fa-sort me-1"></i>Sort Order
                                    </label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" 
                                           min="1" placeholder="1">
                                    <div class="form-help">Lower numbers appear first</div>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active Feature
                                        </label>
                                    </div>
                                    <div class="form-help">Enable this feature to display on the homepage</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Feature Preview</label>
                            <div id="feature-preview" class="text-center p-4 border rounded">
                                <div class="feature-icon mb-3">
                                    <i id="previewIcon" class="{{ old('icon', 'fas fa-star') }} fs-1" 
                                       style="color: {{ old('color', '#0d6efd') }} !important;"></i>
                                </div>
                                <h5 id="previewTitle" class="fw-bold mb-3">
                                    {{ old('title', 'Sample Feature Title') }}
                                </h5>
                                <h6 id="previewSubtitle" class="text-muted mb-2" style="display: {{ old('subtitle') ? 'block' : 'none' }}">
                                    {{ old('subtitle', 'Sample Subtitle') }}
                                </h6>
                                <p id="previewDescription" class="text-muted">
                                    {{ old('description', 'This is a sample description of your feature. It will be updated as you type.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Feature
                        </button>
                        <a href="{{ contextRoute('homepage-features.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Feature Guidelines</h6>
                <ul>
                    <li>Title is required and should be compelling</li>
                    <li>Use clear and concise language for subtitles</li>
                    <li>Provide a detailed description if necessary</li>
                    <li>Choose appropriate icons from Font Awesome</li>
                    <li>Set a sort order for display sequence</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Design Best Practices</h6>
                <ul>
                    <li>Use consistent styling across features</li>
                    <li>Choose icons that match your content</li>
                    <li>Ensure text is readable and engaging</li>
                    <li>Test features on different screen sizes</li>
                    <li>Maintain brand consistency</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('homepage-features.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Features
                    </a>
                    <a href="{{ contextRoute('homepage-features.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Features List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Live preview updates
    $('#title').on('input', function() {
        const title = $(this).val() || 'Sample Feature Title';
        $('#previewTitle').text(title);
    });

    $('#subtitle').on('input', function() {
        const subtitle = $(this).val();
        if (subtitle) {
            $('#previewSubtitle').text(subtitle).show();
        } else {
            $('#previewSubtitle').hide();
        }
    });

    $('#description').on('input', function() {
        const description = $(this).val() || 'This is a sample description of your feature. It will be updated as you type.';
        $('#previewDescription').text(description);
    });

    $('#icon').on('input', function() {
        const icon = $(this).val() || 'fas fa-star';
        $('#iconPreview').attr('class', icon);
        $('#previewIcon').attr('class', icon + ' fs-1').css('color', $('#color').val() + ' !important');
    });

    $('#color').on('input', function() {
        const color = $(this).val();
        $('#color_text').val(color);
        $('#previewIcon').css('color', color + ' !important');
    });

    // Icon preview button
    $('#iconPreviewBtn').click(function() {
        const icon = $('#icon').val();
        if (icon) {
            $('#iconPreview').attr('class', icon);
            $('#previewIcon').attr('class', icon + ' fs-1').css('color', $('#color').val() + ' !important');
        }
    });
});
</script>
@endpush
