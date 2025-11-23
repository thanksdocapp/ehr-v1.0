@extends('admin.layouts.app')

@section('title', 'Meta Tags Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('seo.index') }}">SEO Management</a></li>
    <li class="breadcrumb-item active">Meta Tags Settings</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Meta Tags Settings</h1>
        <p class="page-subtitle text-muted">Configure default meta tags and social media settings</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ contextRoute('seo.meta-tags') }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Default Meta Tags Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-globe me-2"></i>Default Meta Tags</h4>
                        <small class="opacity-75">Basic SEO settings for your website</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_title" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Default Title *
                                    </label>
                                    <input type="text" class="form-control @error('default_title') is-invalid @enderror" 
                                           id="default_title" name="default_title" 
                                           value="{{ old('default_title', $settings->default_title ?? '') }}" 
                                           placeholder="ThanksDoc EHR - Quality Healthcare" maxlength="60">
                                    <div class="form-help">Maximum 60 characters for optimal SEO</div>
                                    @error('default_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_keywords" class="form-label">
                                        <i class="fas fa-key me-1"></i>Default Keywords
                                    </label>
                                    <input type="text" class="form-control @error('default_keywords') is-invalid @enderror" 
                                           id="default_keywords" name="default_keywords" 
                                           value="{{ old('default_keywords', $settings->default_keywords ?? '') }}" 
                                           placeholder="hospital, healthcare, medical, doctor">
                                    <div class="form-help">Separate keywords with commas</div>
                                    @error('default_keywords')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="default_description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Default Description
                            </label>
                            <textarea class="form-control @error('default_description') is-invalid @enderror" 
                                      id="default_description" name="default_description" rows="3" 
                                      placeholder="Your trusted healthcare partner providing quality medical services with experienced professionals and state-of-the-art facilities." maxlength="160">{{ old('default_description', $settings->default_description ?? '') }}</textarea>
                            <div class="form-help">Maximum 160 characters for better search results</div>
                            @error('default_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Open Graph Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fab fa-facebook me-2"></i>Open Graph (Facebook) Settings</h4>
                        <small class="opacity-75">Optimize how your content appears on Facebook</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="og_title" class="form-label">
                                        <i class="fas fa-font me-1"></i>OG Title
                                    </label>
                                    <input type="text" class="form-control @error('og_title') is-invalid @enderror" 
                                           id="og_title" name="og_title" 
                                           value="{{ old('og_title', $settings->og_title ?? '') }}" 
                                           placeholder="ThanksDoc EHR - Quality Healthcare">
                                    <div class="form-help">Title for social media sharing</div>
                                    @error('og_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="og_image" class="form-label">
                                        <i class="fas fa-image me-1"></i>OG Image URL
                                    </label>
                                    <input type="url" class="form-control @error('og_image') is-invalid @enderror" 
                                           id="og_image" name="og_image" 
                                           value="{{ old('og_image', $settings->og_image ?? '') }}" 
                                           placeholder="https://yoursite.com/images/og-image.jpg">
                                    <div class="form-help">Recommended: 1200x630px</div>
                                    @error('og_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="og_description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>OG Description
                            </label>
                            <textarea class="form-control @error('og_description') is-invalid @enderror" 
                                      id="og_description" name="og_description" rows="2" 
                                      placeholder="Quality healthcare services with experienced medical professionals." maxlength="160">{{ old('og_description', $settings->og_description ?? '') }}</textarea>
                            <div class="form-help">Description for social media sharing</div>
                            @error('og_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Twitter Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fab fa-twitter me-2"></i>Twitter Card Settings</h4>
                        <small class="opacity-75">Customize how your content appears on Twitter</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="twitter_card" class="form-label">
                                        <i class="fas fa-credit-card me-1"></i>Twitter Card Type
                                    </label>
                                    <select class="form-select @error('twitter_card') is-invalid @enderror" 
                                            id="twitter_card" name="twitter_card">
                                        <option value="">Select Card Type</option>
                                        <option value="summary" {{ old('twitter_card', $settings->twitter_card ?? '') == 'summary' ? 'selected' : '' }}>Summary</option>
                                        <option value="summary_large_image" {{ old('twitter_card', $settings->twitter_card ?? '') == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                                        <option value="app" {{ old('twitter_card', $settings->twitter_card ?? '') == 'app' ? 'selected' : '' }}>App</option>
                                        <option value="player" {{ old('twitter_card', $settings->twitter_card ?? '') == 'player' ? 'selected' : '' }}>Player</option>
                                    </select>
                                    @error('twitter_card')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="twitter_site" class="form-label">
                                        <i class="fas fa-at me-1"></i>Twitter Site Handle
                                    </label>
                                    <input type="text" class="form-control @error('twitter_site') is-invalid @enderror" 
                                           id="twitter_site" name="twitter_site" 
                                           value="{{ old('twitter_site', $settings->twitter_site ?? '') }}" 
                                           placeholder="@newwaveshospital">
                                    <div class="form-help">Your Twitter username (with @)</div>
                                    @error('twitter_site')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Analytics & Tracking Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Analytics & Tracking</h4>
                        <small class="opacity-75">Tracking codes for analytics and marketing</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="google_analytics_id" class="form-label">
                                        <i class="fab fa-google me-1"></i>Google Analytics ID
                                    </label>
                                    <input type="text" class="form-control @error('google_analytics_id') is-invalid @enderror" 
                                           id="google_analytics_id" name="google_analytics_id" 
                                           value="{{ old('google_analytics_id', $settings->google_analytics_id ?? '') }}" 
                                           placeholder="G-XXXXXXXXXX">
                                    <div class="form-help">Your Google Analytics tracking ID</div>
                                    @error('google_analytics_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="google_search_console_id" class="form-label">
                                        <i class="fas fa-search me-1"></i>Google Search Console ID
                                    </label>
                                    <input type="text" class="form-control @error('google_search_console_id') is-invalid @enderror" 
                                           id="google_search_console_id" name="google_search_console_id" 
                                           value="{{ old('google_search_console_id', $settings->google_search_console_id ?? '') }}" 
                                           placeholder="google-site-verification=...">
                                    <div class="form-help">Search Console verification meta tag</div>
                                    @error('google_search_console_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="facebook_pixel_id" class="form-label">
                                <i class="fab fa-facebook-square me-1"></i>Facebook Pixel ID
                            </label>
                            <input type="text" class="form-control @error('facebook_pixel_id') is-invalid @enderror" 
                                   id="facebook_pixel_id" name="facebook_pixel_id" 
                                   value="{{ old('facebook_pixel_id', $settings->facebook_pixel_id ?? '') }}" 
                                   placeholder="123456789012345">
                            <div class="form-help">Your Facebook Pixel ID for tracking</div>
                            @error('facebook_pixel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-section text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-save me-2"></i>Save Meta Tags Settings
                    </button>
                    <a href="{{ contextRoute('seo.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Back to SEO
                    </a>
                </div>
            </form>
        </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Meta Tags Preview</h3>
            </div>
            <div class="card-body">
                <div class="meta-preview">
                    <h6 class="text-primary">Search Engine Result</h6>
                    <div class="search-preview">
                        <div class="preview-title">{{ $settings->default_title ?? 'ThanksDoc EHR - Quality Healthcare' }}</div>
                        <div class="preview-url">{{ url('/') }}</div>
                        <div class="preview-description">{{ $settings->default_description ?? 'Your trusted healthcare partner providing quality medical services...' }}</div>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="social-preview">
                    <h6 class="text-primary">Social Media Preview</h6>
                    <div class="facebook-preview">
                        <div class="preview-image">
                            @if($settings->og_image ?? false)
                                <img src="{{ $settings->og_image }}" alt="OG Image" class="img-fluid">
                            @else
                                <div class="placeholder-image">
                                    <i class="fas fa-image fa-2x"></i>
                                    <small>No image set</small>
                                </div>
                            @endif
                        </div>
                        <div class="preview-content">
                            <div class="preview-title">{{ $settings->og_title ?? $settings->default_title ?? 'ThanksDoc EHR' }}</div>
                            <div class="preview-description">{{ $settings->og_description ?? $settings->default_description ?? 'Quality healthcare services...' }}</div>
                            <div class="preview-url">{{ url('/') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">SEO Tips</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Title:</strong> Keep under 60 characters
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Description:</strong> 120-160 characters work best
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Keywords:</strong> Use 3-5 relevant keywords
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>OG Image:</strong> Use 1200x630px for best results
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Consistency:</strong> Keep branding consistent across platforms
                    </li>
                </ul>
            </div>
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
    
    .meta-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .search-preview {
        font-family: arial, sans-serif;
    }
    
    .preview-title {
        color: #1a0dab;
        font-size: 18px;
        line-height: 1.2;
        margin-bottom: 2px;
        text-decoration: none;
        font-weight: normal;
    }
    
    .preview-url {
        color: #006621;
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .preview-description {
        color: #545454;
        font-size: 14px;
        line-height: 1.4;
    }
    
    .social-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
    }
    
    .facebook-preview {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: white;
    }
    
    .preview-image {
        height: 120px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #999;
    }
    
    .preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .placeholder-image {
        text-align: center;
    }
    
    .preview-content {
        padding: 12px;
    }
    
    .facebook-preview .preview-title {
        color: #1d2129;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .facebook-preview .preview-description {
        color: #606770;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .facebook-preview .preview-url {
        color: #606770;
        font-size: 12px;
        text-transform: uppercase;
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
    
    hr {
        border-top: 2px solid #f1f3f4;
        margin: 1.5rem 0;
    }
    
    .card-header h3 {
        color: #495057;
        font-weight: 600;
    }
    
    .card-header h5 {
        color: #667eea;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview updates
    const titleInput = document.getElementById('default_title');
    const descriptionInput = document.getElementById('default_description');
    const ogTitleInput = document.getElementById('og_title');
    const ogDescriptionInput = document.getElementById('og_description');
    const ogImageInput = document.getElementById('og_image');
    
    function updatePreview() {
        const title = titleInput.value || 'ThanksDoc EHR - Quality Healthcare';
        const description = descriptionInput.value || 'Your trusted healthcare partner providing quality medical services...';
        const ogTitle = ogTitleInput.value || title;
        const ogDescription = ogDescriptionInput.value || description;
        const ogImage = ogImageInput.value;
        
        // Update search preview
        document.querySelector('.search-preview .preview-title').textContent = title;
        document.querySelector('.search-preview .preview-description').textContent = description;
        
        // Update social preview
        document.querySelector('.facebook-preview .preview-title').textContent = ogTitle;
        document.querySelector('.facebook-preview .preview-description').textContent = ogDescription;
        
        // Update OG image
        const imageContainer = document.querySelector('.preview-image');
        if (ogImage) {
            imageContainer.innerHTML = `<img src="${ogImage}" alt="OG Image" class="img-fluid">`;
        } else {
            imageContainer.innerHTML = `
                <div class="placeholder-image">
                    <i class="fas fa-image fa-2x"></i>
                    <small>No image set</small>
                </div>
            `;
        }
    }
    
    // Add event listeners for live preview
    titleInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    ogTitleInput.addEventListener('input', updatePreview);
    ogDescriptionInput.addEventListener('input', updatePreview);
    ogImageInput.addEventListener('input', updatePreview);
    
    // Character count warnings
    function addCharacterCount(input, maxLength) {
        const container = input.parentElement;
        const counter = document.createElement('div');
        counter.className = 'character-count text-end mt-1';
        counter.style.fontSize = '0.875rem';
        
        function updateCount() {
            const length = input.value.length;
            counter.textContent = `${length}/${maxLength}`;
            
            if (length > maxLength) {
                counter.className = 'character-count text-end mt-1 text-danger';
            } else if (length > maxLength * 0.8) {
                counter.className = 'character-count text-end mt-1 text-warning';
            } else {
                counter.className = 'character-count text-end mt-1 text-muted';
            }
        }
        
        input.addEventListener('input', updateCount);
        container.appendChild(counter);
        updateCount();
    }
    
    // Add character counters
    addCharacterCount(titleInput, 60);
    addCharacterCount(descriptionInput, 160);
    addCharacterCount(ogTitleInput, 60);
    addCharacterCount(ogDescriptionInput, 160);
});
</script>
@endpush
