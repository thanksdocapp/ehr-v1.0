@extends('admin.layouts.app')

@section('title', 'Create Banner Slide')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('banner-slides.index') }}">Banner Slides</a></li>
    <li class="breadcrumb-item active">Create Banner Slide</li>
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

.image-preview {
    border: 2px dashed #e3e6f0;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.image-preview:hover {
    border-color: #1cc88a;
    background-color: #f8f9fc;
}

.image-preview img {
    max-width: 100%;
    max-height: 180px;
    border-radius: 8px;
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
        <h1><i class="fas fa-plus me-2 text-primary"></i>Create Banner Slide</h1>
        <p class="page-subtitle text-muted">Add a new banner slide to your website</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createBannerSlideForm" method="POST" action="{{ contextRoute('banner-slides.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Slide Content Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Slide Content</h4>
                        <small class="opacity-75">Banner slide text content and messaging</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Title *
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Enter slide title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="subtitle" class="form-label">
                                <i class="fas fa-text-height me-1"></i>Subtitle
                            </label>
                            <textarea class="form-control @error('subtitle') is-invalid @enderror" 
                                      id="subtitle" name="subtitle" rows="3" 
                                      placeholder="Enter slide subtitle">{{ old('subtitle') }}</textarea>
                            <div class="form-help">Brief subtitle or tagline for the slide</div>
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Enter slide description">{{ old('description') }}</textarea>
                            <div class="form-help">Detailed description of the slide content</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Call to Action Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-mouse-pointer me-2"></i>Call to Action</h4>
                        <small class="opacity-75">Button settings and action links</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="button_text" class="form-label">
                                        <i class="fas fa-hand-pointer me-1"></i>Button Text
                                    </label>
                                    <input type="text" class="form-control @error('button_text') is-invalid @enderror" 
                                           id="button_text" name="button_text" value="{{ old('button_text') }}" 
                                           placeholder="e.g., Learn More">
                                    @error('button_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="button_url" class="form-label">
                                        <i class="fas fa-link me-1"></i>Button URL
                                    </label>
                                    <input type="text" class="form-control @error('button_url') is-invalid @enderror" 
                                           id="button_url" name="button_url" value="{{ old('button_url') }}" 
                                           placeholder="/about, /contact, or https://example.com">
                                    <div class="form-help">Enter relative URL (e.g., /about, /contact) or full URL (e.g., https://example.com)</div>
                                    @error('button_url')
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
                        <small class="opacity-75">Slide appearance and ordering</small>
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

                                <div class="form-group">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image me-1"></i>Banner Image
                                    </label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                                    <div class="form-help">Upload banner image (JPG, PNG, GIF, WebP - Max: 2MB)</div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="background_color" class="form-label">
                                        <i class="fas fa-palette me-1"></i>Background Color
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control color-preview @error('background_color') is-invalid @enderror" 
                                               id="background_color" name="background_color" value="{{ old('background_color', '#ffffff') }}" 
                                               style="width: 60px; height: 40px; padding: 0;">
                                        <input type="text" class="form-control ms-2 @error('background_color') is-invalid @enderror" 
                                               id="background_color_text" value="{{ old('background_color', '#ffffff') }}" 
                                               placeholder="#ffffff" readonly>
                                    </div>
                                    @error('background_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active Slide
                                        </label>
                                    </div>
                                    <div class="form-help">Enable this slide to display on the website</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Image Preview</label>
                            <div id="image-preview" class="image-preview">
                                <i class="fas fa-image text-muted fa-3x"></i>
                                <p class="text-muted mt-2">No image selected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Banner Slide
                        </button>
                        <a href="{{ contextRoute('banner-slides.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Banner Slide Guidelines</h6>
                <ul>
                    <li>Title is required and should be compelling</li>
                    <li>Use high-quality images for best results</li>
                    <li>Keep text concise and readable</li>
                    <li>Ensure proper contrast with background colors</li>
                    <li>Test button links before publishing</li>
                    <li>Use appropriate sort order for slide sequence</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h6><i class="fas fa-link me-2"></i>Button URL Examples</h6>
                <ul>
                    <li><strong>Relative URLs:</strong></li>
                    <li><code>/about</code> - About page</li>
                    <li><code>/contact</code> - Contact page</li>
                    <li><code>/services</code> - Services page</li>
                    <li><code>/appointments/book</code> - Book appointment</li>
                    <li><strong>External URLs:</strong></li>
                    <li><code>https://example.com</code> - External website</li>
                    <li><code>tel:+1234567890</code> - Phone number</li>
                    <li><code>mailto:info@hospital.com</code> - Email address</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Design Best Practices</h6>
                <ul>
                    <li>Use consistent styling across all slides</li>
                    <li>Optimize images for web performance</li>
                    <li>Keep text overlay areas clear and readable</li>
                    <li>Use compelling call-to-action text</li>
                    <li>Test slides on different screen sizes</li>
                    <li>Maintain brand consistency</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-palette me-2"></i>Color Guidelines</h6>
                <ul>
                    <li>Choose colors that match your brand</li>
                    <li>Ensure good contrast for readability</li>
                    <li>Consider accessibility requirements</li>
                    <li>Use neutral colors as safe defaults</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('banner-slides.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Slides
                    </a>
                    <a href="{{ contextRoute('banner-slides.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Slides List
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
    // Image preview
    $('#image').change(function(e) {
        const file = e.target.files[0];
        const preview = $('#image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html(`
                    <img src="${e.target.result}" alt="Preview" class="img-fluid rounded">
                `);
            }
            reader.readAsDataURL(file);
        } else {
            preview.html(`
                <i class="fas fa-image text-muted fa-3x"></i>
                <p class="text-muted mt-2">No image selected</p>
            `);
        }
    });

    // Color picker synchronization
    $('#background_color').on('change', function() {
        const color = $(this).val();
        $('#background_color_text').val(color);
    });

    $('#background_color_text').on('input', function() {
        const color = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $('#background_color').val(color);
        }
    });

    // Form validation
    $('#createBannerSlideForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $('.form-control[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate URL format (allow relative URLs starting with / or valid absolute URLs)
        const buttonUrl = $('#button_url').val();
        if (buttonUrl && !isValidURLOrPath(buttonUrl)) {
            $('#button_url').addClass('is-invalid');
            isValid = false;
        }

        // Validate sort order
        const sortOrder = parseInt($('#sort_order').val());
        if (sortOrder && sortOrder < 1) {
            $('#sort_order').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // URL validation helper (allow both relative paths and full URLs)
    function isValidURLOrPath(string) {
        // Check if it's a relative path (starts with /)
        if (string.startsWith('/')) {
            return true;
        }
        
        // Check if it's a valid absolute URL
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Remove validation errors on input
    $('.form-control').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Character counter for text fields
    $('textarea').on('input', function() {
        const maxLength = $(this).attr('id') === 'description' ? 500 : 200;
        const currentLength = $(this).val().length;
        
        if (!$(this).next('.char-counter').length) {
            $(this).after('<small class="text-muted char-counter"></small>');
        }
        
        $(this).next('.char-counter').text(currentLength + '/' + maxLength + ' characters');
        
        if (currentLength > maxLength) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
