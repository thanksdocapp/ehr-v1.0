@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Edit About Us Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('about.index') }}">About Us</a></li>
    <li class="breadcrumb-item active">Edit Settings</li>
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
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.image-preview:hover {
    border-color: #1cc88a;
    background-color: #f8f9fc;
}

.image-preview img, .current-image-display img {
    max-width: 100%;
    max-height: 150px;
    border-radius: 8px;
}

.current-image-display {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    background-color: #f8f9fc;
}

.status-indicator {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-complete {
    background-color: #d4edda;
    color: #155724;
}

.status-incomplete {
    background-color: #f8d7da;
    color: #721c24;
}

.status-default {
    background-color: #fff3cd;
    color: #856404;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit About Us Settings</h1>
        <p class="page-subtitle text-muted">Update your hospital's About Us page content and settings</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="aboutUsForm" action="{{ contextRoute('about.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Hero Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-star me-2"></i>Hero Section</h4>
                        <small class="opacity-75">Main banner content for the About Us page</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="about_hero_title" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Hero Title *
                                    </label>
                                    <input type="text" class="form-control @error('about_hero_title') is-invalid @enderror" 
                                           id="about_hero_title" name="about_hero_title" 
                                           value="{{ old('about_hero_title', $settings['about_hero_title'] ?? '') }}" 
                                           placeholder="Enter hero title" required>
                                    @error('about_hero_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="about_main_title" class="form-label">
                                        <i class="fas fa-text-width me-1"></i>Main Title *
                                    </label>
                                    <input type="text" class="form-control @error('about_main_title') is-invalid @enderror" 
                                           id="about_main_title" name="about_main_title" 
                                           value="{{ old('about_main_title', $settings['about_main_title'] ?? '') }}" 
                                           placeholder="Enter main title" required>
                                    @error('about_main_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="about_hero_subtitle" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Hero Subtitle *
                            </label>
                            <textarea class="form-control @error('about_hero_subtitle') is-invalid @enderror" 
                                      id="about_hero_subtitle" name="about_hero_subtitle" rows="3" 
                                      placeholder="Enter hero subtitle" required>{{ old('about_hero_subtitle', $settings['about_hero_subtitle'] ?? '') }}</textarea>
                            <div class="form-help">Brief description displayed in the hero section</div>
                            @error('about_hero_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Main Content Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Main Content</h4>
                        <small class="opacity-75">Detailed content for the About Us page</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="about_main_description" class="form-label">
                                <i class="fas fa-paragraph me-1"></i>Main Description *
                            </label>
                            <textarea class="form-control @error('about_main_description') is-invalid @enderror" 
                                      id="about_main_description" name="about_main_description" rows="4" 
                                      placeholder="Enter main description" required>{{ old('about_main_description', $settings['about_main_description'] ?? '') }}</textarea>
                            <div class="form-help">Short description of your hospital or organization</div>
                            @error('about_main_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="about_main_content" class="form-label">
                                <i class="fas fa-edit me-1"></i>Main Content *
                            </label>
                            <textarea class="form-control @error('about_main_content') is-invalid @enderror" 
                                      id="about_main_content" name="about_main_content" rows="6" 
                                      placeholder="Enter detailed content about your hospital" required>{{ old('about_main_content', $settings['about_main_content'] ?? '') }}</textarea>
                            <div class="form-help">Detailed content about your hospital, mission, values, etc.</div>
                            @error('about_main_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Image Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-image me-2"></i>Image Settings</h4>
                        <small class="opacity-75">Upload and manage About Us page image</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="about_image" class="form-label">
                                        <i class="fas fa-upload me-1"></i>About Us Image
                                    </label>
                                    <input type="file" class="form-control @error('about_image') is-invalid @enderror" 
                                           id="about_image" name="about_image" accept="image/*">
                                    <div class="form-help">Supported formats: JPEG, PNG, JPG, GIF, WebP (Max: 2MB)</div>
                                    @error('about_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="about_image_alt" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Image Alt Text
                                    </label>
                                    <input type="text" class="form-control @error('about_image_alt') is-invalid @enderror" 
                                           id="about_image_alt" name="about_image_alt" 
                                           value="{{ old('about_image_alt', $settings['about_image_alt'] ?? '') }}" 
                                           placeholder="Describe the image">
                                    <div class="form-help">For accessibility and SEO</div>
                                    @error('about_image_alt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Current Image</label>
                                    <div class="current-image-display">
                                        @if($settings && isset($settings['about_image']))
                                            @if(str_starts_with($settings['about_image'], 'assets/'))
                                                <img src="{{ asset($settings['about_image']) }}" 
                                                     alt="{{ $settings['about_image_alt'] ?? 'About Us Image' }}">
                                            @else
                                                <img src="{{ Storage::disk('public')->url($settings['about_image']) }}" 
                                                     alt="{{ $settings['about_image_alt'] ?? 'About Us Image' }}">
                                            @endif
                                            <p class="mt-2 text-muted small">Alt Text: {{ $settings['about_image_alt'] ?? 'Not set' }}</p>
                                        @else
                                            <i class="fas fa-image text-muted fa-3x"></i>
                                            <p class="text-muted mt-2">No image uploaded</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">New Image Preview</label>
                                    <div id="image-preview" class="image-preview">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                        <p class="text-muted mt-2">No new image selected</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update About Us Settings
                        </button>
                        <a href="{{ contextRoute('about.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Content Status</h6>
                <div class="mb-3">
                    <strong>Hero Title:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['about_hero_title']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['about_hero_title']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Hero Subtitle:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['about_hero_subtitle']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['about_hero_subtitle']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Main Title:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['about_main_title']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['about_main_title']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Main Content:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['about_main_content']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['about_main_content']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Image:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['about_image']) ? 'status-complete' : 'status-default' }}">
                        {{ $settings && isset($settings['about_image']) ? 'Uploaded' : 'Default' }}
                    </span>
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Content Guidelines</h6>
                <ul>
                    <li>Keep hero title concise and compelling</li>
                    <li>Hero subtitle should summarize your mission</li>
                    <li>Main description should be 2-3 sentences</li>
                    <li>Main content can include detailed information</li>
                    <li>Use high-quality images that represent your hospital</li>
                    <li>Always provide alt text for images</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-palette me-2"></i>Design Tips</h6>
                <ul>
                    <li>Use professional, welcoming language</li>
                    <li>Keep content engaging and informative</li>
                    <li>Highlight your hospital's unique qualities</li>
                    <li>Include your mission and values</li>
                    <li>Consider patient perspective when writing</li>
                    <li>Update content regularly to stay current</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('about.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>View Current Content
                    </a>
                    <a href="{{ contextRoute('about.resetImage') }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-undo me-1"></i>Reset Image to Default
                    </a>
                    <a href="{{ contextRoute('about.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to About Us
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
    $('#about_image').change(function(e) {
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
                <p class="text-muted mt-2">No new image selected</p>
            `);
        }
    });

    // Form validation
    $('#aboutUsForm').on('submit', function(e) {
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

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Remove validation errors on input
    $('.form-control').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Character counter for text areas
    $('textarea').on('input', function() {
        const maxLength = $(this).attr('id') === 'about_main_content' ? 1000 : 
                         $(this).attr('id') === 'about_main_description' ? 500 : 300;
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

    // Auto-resize textareas
    $('textarea').each(function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Initialize character counters
    $('textarea').trigger('input');
});
</script>
@endpush
