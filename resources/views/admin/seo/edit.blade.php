@extends('admin.layouts.app')

@section('title', 'Edit SEO Page')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('seo.index') }}">SEO Management</a></li>
    <li class="breadcrumb-item active">Edit SEO Page</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-search me-2 text-primary"></i>Edit SEO Page</h1>
        <p class="page-subtitle text-muted">Modify the SEO details for this page</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editSEOForm" action="{{ contextRoute('seo.pages.update', $page->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Page Basic Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Page Information</h4>
                        <small class="opacity-75">Basic page details and URL configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Page Title *
                                    </label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $page->title) }}" 
                                           placeholder="Enter page title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="url" class="form-label">
                                        <i class="fas fa-link me-1"></i>Page URL *
                                    </label>
                                    <input type="text" class="form-control @error('url') is-invalid @enderror" 
                                           id="url" name="url" value="{{ old('url', $page->url) }}" 
                                           placeholder="/page-url" required>
                                    <div class="form-help">Relative URL starting with /</div>
                                    @error('url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-eye me-1"></i>Active (include in sitemap)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Meta Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-tags me-2"></i>SEO Meta Information</h4>
                        <small class="opacity-75">Search engine optimization details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meta_title" class="form-label">
                                        <i class="fas fa-font me-1"></i>Meta Title
                                    </label>
                                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                                           id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" 
                                           placeholder="SEO optimized title" maxlength="60">
                                    <div class="form-help">
                                        <span id="meta_title_count">0</span>/60 characters - Keep it under 60 for optimal display
                                    </div>
                                    @error('meta_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="canonical_url" class="form-label">
                                        <i class="fas fa-external-link-alt me-1"></i>Canonical URL
                                    </label>
                                    <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" 
                                           id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" 
                                           placeholder="https://example.com/canonical-url">
                                    <div class="form-help">Preferred URL for search engines</div>
                                    @error('canonical_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Meta Description
                            </label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" name="meta_description" rows="3" 
                                      placeholder="Brief description for search engines" maxlength="160">{{ old('meta_description', $page->meta_description) }}</textarea>
                            <div class="form-help">
                                <span id="meta_description_count">0</span>/160 characters - Compelling snippet for search results
                            </div>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_keywords" class="form-label">
                                <i class="fas fa-key me-1"></i>Meta Keywords
                            </label>
                            <textarea class="form-control @error('meta_keywords') is-invalid @enderror" 
                                      id="meta_keywords" name="meta_keywords" rows="2" 
                                      placeholder="keyword1, keyword2, keyword3">{{ old('meta_keywords', $page->meta_keywords) }}</textarea>
                            <div class="form-help">Separate keywords with commas - Use relevant terms naturally</div>
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-section text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-save me-2"></i>Update SEO Page
                    </button>
                    <a href="{{ contextRoute('seo.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">SEO Guidelines</h3>
                </div>
                <div class="card-body">
                    <div class="guideline-item">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Page Title</h6>
                        <p class="small text-muted">Keep it descriptive and under 60 characters</p>
                    </div>
                    
                    <div class="guideline-item">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Meta Title</h6>
                        <p class="small text-muted">Should be 50-60 characters for optimal display</p>
                    </div>
                    
                    <div class="guideline-item">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Meta Description</h6>
                        <p class="small text-muted">120-160 characters, compelling and descriptive</p>
                    </div>
                    
                    <div class="guideline-item">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>URL Structure</h6>
                        <p class="small text-muted">Keep URLs short, descriptive, and SEO-friendly</p>
                    </div>
                    
                    <div class="guideline-item">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Keywords</h6>
                        <p class="small text-muted">Use relevant keywords naturally, avoid keyword stuffing</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">SEO Score Preview</h3>
                </div>
                <div class="card-body">
                    <div class="seo-score-preview">
                        <div class="score-circle" id="seo-score-circle">
                            <span class="score-value" id="seo-score-value">0</span>
                            <small>SEO Score</small>
                        </div>
                        <div class="score-breakdown mt-3">
                            <div class="score-item">
                                <span class="label">Title:</span>
                                <span class="value" id="title-score">0/20</span>
                            </div>
                            <div class="score-item">
                                <span class="label">Meta Title:</span>
                                <span class="value" id="meta-title-score">0/20</span>
                            </div>
                            <div class="score-item">
                                <span class="label">Meta Description:</span>
                                <span class="value" id="meta-description-score">0/25</span>
                            </div>
                            <div class="score-item">
                                <span class="label">Keywords:</span>
                                <span class="value" id="keywords-score">0/15</span>
                            </div>
                            <div class="score-item">
                                <span class="label">Canonical URL:</span>
                                <span class="value" id="canonical-score">0/10</span>
                            </div>
                            <div class="score-item">
                                <span class="label">URL Structure:</span>
                                <span class="value" id="url-score">0/10</span>
                            </div>
                        </div>
                    </div>
                </div>
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
    
    .guideline-item {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .guideline-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .seo-score-preview {
        text-align: center;
    }
    
    .score-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#4CAF50 0deg, #4CAF50 0deg, #f0f0f0 0deg);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .score-circle::before {
        content: '';
        position: absolute;
        width: 90px;
        height: 90px;
        background: white;
        border-radius: 50%;
        z-index: 1;
    }
    
    .score-value {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        z-index: 2;
        position: relative;
    }
    
    .score-circle small {
        font-size: 0.75rem;
        color: #666;
        z-index: 2;
        position: relative;
    }
    
    .score-breakdown {
        text-align: left;
    }
    
    .score-item {
        display: flex;
        justify-content: space-between;
        padding: 0.25rem 0;
        font-size: 0.875rem;
    }
    
    .score-item .label {
        color: #666;
    }
    
    .score-item .value {
        font-weight: 600;
        color: #333;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const urlInput = document.getElementById('url');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    const metaKeywordsInput = document.getElementById('meta_keywords');
    const canonicalUrlInput = document.getElementById('canonical_url');
    
    // Character counters
    const metaTitleCount = document.getElementById('meta_title_count');
    const metaDescriptionCount = document.getElementById('meta_description_count');
    
    // Update character counters
    function updateCharacterCount(input, counter) {
        counter.textContent = input.value.length;
    }
    
    function updateSeoScore() {
        let score = 0;
        let breakdown = {
            title: 0,
            metaTitle: 0,
            metaDescription: 0,
            keywords: 0,
            canonical: 0,
            url: 0
        };
        
        // Title scoring (20 points)
        if (titleInput.value) {
            breakdown.title += 10;
            if (titleInput.value.length >= 10 && titleInput.value.length <= 60) {
                breakdown.title += 10;
            }
        }
        
        // Meta title scoring (20 points)
        if (metaTitleInput.value) {
            breakdown.metaTitle += 10;
            if (metaTitleInput.value.length >= 10 && metaTitleInput.value.length <= 60) {
                breakdown.metaTitle += 10;
            }
        }
        
        // Meta description scoring (25 points)
        if (metaDescriptionInput.value) {
            breakdown.metaDescription += 15;
            if (metaDescriptionInput.value.length >= 120 && metaDescriptionInput.value.length <= 160) {
                breakdown.metaDescription += 10;
            }
        }
        
        // Keywords scoring (15 points)
        if (metaKeywordsInput.value) {
            breakdown.keywords = 15;
        }
        
        // Canonical URL scoring (10 points)
        if (canonicalUrlInput.value) {
            breakdown.canonical = 10;
        }
        
        // URL structure scoring (10 points)
        if (urlInput.value) {
            if (urlInput.value.length < 100 && !urlInput.value.includes('?')) {
                breakdown.url = 10;
            }
        }
        
        score = breakdown.title + breakdown.metaTitle + breakdown.metaDescription + 
                breakdown.keywords + breakdown.canonical + breakdown.url;
        
        // Update UI
        updateScoreDisplay(score, breakdown);
    }
    
    function updateScoreDisplay(score, breakdown) {
        const scoreValue = document.getElementById('seo-score-value');
        const scoreCircle = document.getElementById('seo-score-circle');
        
        scoreValue.textContent = score;
        
        // Update circle color based on score
        let color = '#f44336'; // Red
        if (score >= 80) color = '#4CAF50'; // Green
        else if (score >= 60) color = '#FF9800'; // Orange
        
        const percentage = (score / 100) * 360;
        scoreCircle.style.background = `conic-gradient(${color} ${percentage}deg, #f0f0f0 ${percentage}deg)`;
        
        // Update breakdown
        document.getElementById('title-score').textContent = `${breakdown.title}/20`;
        document.getElementById('meta-title-score').textContent = `${breakdown.metaTitle}/20`;
        document.getElementById('meta-description-score').textContent = `${breakdown.metaDescription}/25`;
        document.getElementById('keywords-score').textContent = `${breakdown.keywords}/15`;
        document.getElementById('canonical-score').textContent = `${breakdown.canonical}/10`;
        document.getElementById('url-score').textContent = `${breakdown.url}/10`;
    }

    // Initialize character counts
    metaTitleInput.addEventListener('input', function() {
        updateCharacterCount(this, metaTitleCount);
        updateSeoScore();
    });

    metaDescriptionInput.addEventListener('input', function() {
        updateCharacterCount(this, metaDescriptionCount);
        updateSeoScore();
    });

    [titleInput, urlInput, metaKeywordsInput, canonicalUrlInput].forEach(input => {
        input.addEventListener('input', updateSeoScore);
    });

    updateCharacterCount(metaTitleInput, metaTitleCount);
    updateCharacterCount(metaDescriptionInput, metaDescriptionCount);
    updateSeoScore();
});
</script>
@endpush
