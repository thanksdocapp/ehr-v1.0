@extends('admin.layouts.app')

@section('title', 'Robots.txt Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('seo.index') }}">SEO Management</a></li>
    <li class="breadcrumb-item active">Robots.txt Management</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-robot me-2 text-primary"></i>Robots.txt Management</h1>
        <p class="page-subtitle text-muted">Configure robots.txt file to control search engine crawling</p>
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
            <form action="{{ contextRoute('seo.robots') }}" method="POST">
                @csrf
                @method('POST')
                
                <!-- Robots.txt Editor Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Robots.txt Editor</h4>
                        <small class="opacity-75">Configure search engine crawling rules</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="robots_content" class="form-label">
                                <i class="fas fa-file-code me-1"></i>Robots.txt Content
                            </label>
                            <textarea class="form-control @error('robots_content') is-invalid @enderror" 
                                      id="robots_content" name="robots_content" rows="15" 
                                      placeholder="User-agent: *&#10;Allow: /&#10;&#10;Sitemap: {{ url('sitemap.xml') }}">{{ old('robots_content', $robotsContent ?? '') }}</textarea>
                            <div class="form-help">
                                Configure how search engines should crawl your website. Be careful with changes as they can affect SEO.
                            </div>
                            @error('robots_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
                                    <i class="fas fa-undo me-2"></i>Reset to Default
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="loadTemplate('restrictive')">
                                    <i class="fas fa-shield-alt me-2"></i>Restrictive Template
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="loadTemplate('blog')">
                                    <i class="fas fa-blog me-2"></i>Blog Template
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="loadTemplate('ecommerce')">
                                    <i class="fas fa-shopping-cart me-2"></i>E-commerce Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- File Preview Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Live Preview</h4>
                        <small class="opacity-75">Real-time preview of your robots.txt file</small>
                    </div>
                    <div class="form-section-body">
                        <div class="robots-preview">
                            <div class="preview-header">
                                <strong>File Location:</strong> 
                                <a href="{{ url('robots.txt') }}" target="_blank" class="text-decoration-none">
                                    {{ url('robots.txt') }}
                                    <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            </div>
                            <div class="preview-content">
                                <pre id="robots-preview-content">{{ $robotsContent ?? "User-agent: *\nAllow: /\n\nSitemap: " . url('sitemap.xml') }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-section text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-save me-2"></i>Save Robots.txt
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
                <h3 class="card-title mb-0">Robots.txt Information</h3>
            </div>
            <div class="card-body">
                <div class="info-section">
                    <h6 class="text-primary">What is robots.txt?</h6>
                    <p class="text-muted small">
                        The robots.txt file tells search engine crawlers which pages or files they can or can't request from your site. It's a standard used by websites to communicate with web crawlers.
                    </p>
                </div>
                
                <div class="info-section">
                    <h6 class="text-primary">File Status</h6>
                    <div class="status-info">
                        <div class="status-item">
                            <i class="fas fa-link text-success me-2"></i>
                            <span>Accessible at: <code>/robots.txt</code></span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-robot text-info me-2"></i>
                            <span>Automatically includes sitemap reference</span>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h6 class="text-primary">Current Sitemap</h6>
                    <div class="sitemap-info">
                        <code>Sitemap: {{ url('sitemap.xml') }}</code>
                        <small class="text-muted d-block mt-1">
                            This will be automatically included in your robots.txt
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Common Directives</h3>
            </div>
            <div class="card-body">
                <div class="directive-examples">
                    <div class="directive-item">
                        <h6>Allow All Crawlers</h6>
                        <code>User-agent: *<br>Allow: /</code>
                        <small class="text-muted d-block mt-1">Allows all search engines to crawl all pages</small>
                    </div>
                    
                    <div class="directive-item">
                        <h6>Block Specific Directories</h6>
                        <code>User-agent: *<br>Disallow: /admin/<br>Disallow: /private/</code>
                        <small class="text-muted d-block mt-1">Blocks access to admin and private directories</small>
                    </div>
                    
                    <div class="directive-item">
                        <h6>Block Specific Bot</h6>
                        <code>User-agent: BadBot<br>Disallow: /</code>
                        <small class="text-muted d-block mt-1">Blocks a specific bot from crawling your site</small>
                    </div>
                    
                    <div class="directive-item">
                        <h6>Crawl Delay</h6>
                        <code>User-agent: *<br>Crawl-delay: 10</code>
                        <small class="text-muted d-block mt-1">Adds a 10-second delay between requests</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Quick Templates</h3>
            </div>
            <div class="card-body">
                <div class="template-buttons">
                    <button class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="loadTemplate('default')">
                        <i class="fas fa-globe me-2"></i>Default (Allow All)
                    </button>
                    <button class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="loadTemplate('restrictive')">
                        <i class="fas fa-shield-alt me-2"></i>Restrictive
                    </button>
                    <button class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="loadTemplate('blog')">
                        <i class="fas fa-blog me-2"></i>Blog/CMS
                    </button>
                    <button class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="loadTemplate('ecommerce')">
                        <i class="fas fa-shopping-cart me-2"></i>E-commerce
                    </button>
                </div>
                
                <hr class="my-3">
                
                <div class="validation-info">
                    <h6 class="text-primary">Validation</h6>
                    <p class="text-muted small mb-2">
                        Test your robots.txt file using Google's robots.txt Tester tool:
                    </p>
                    <a href="https://www.google.com/webmasters/tools/robots-testing-tool" 
                       target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fab fa-google me-2"></i>Test with Google
                    </a>
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
    
    .form-control {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        line-height: 1.4;
    }
    
    .form-control:focus {
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
    
    .robots-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
    }
    
    .preview-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .preview-content {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
    }
    
    .preview-content pre {
        background: none;
        border: none;
        padding: 0;
        margin: 0;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.4;
        color: #495057;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .info-section {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .info-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .info-section h6 {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .status-info {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
    }
    
    .status-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .status-item:last-child {
        margin-bottom: 0;
    }
    
    .status-item span {
        font-size: 0.9rem;
        color: #495057;
    }
    
    .sitemap-info {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
    }
    
    .directive-examples {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .directive-item {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .directive-item h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    .directive-item code {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px;
        display: block;
        font-size: 0.85rem;
        line-height: 1.4;
        color: #e83e8c;
    }
    
    .template-buttons .btn {
        text-align: left;
        padding: 12px 16px;
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
    
    .btn-outline-primary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-control {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.4;
    }
    
    .card-header h3 {
        color: #495057;
        font-weight: 600;
    }
    
    .validation-info h6 {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.875rem;
        color: #e83e8c;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('robots_content');
    const previewContent = document.getElementById('robots-preview-content');
    
    // Update preview when content changes
    textarea.addEventListener('input', function() {
        previewContent.textContent = this.value;
    });
    
    // Initial preview update
    if (textarea.value) {
        previewContent.textContent = textarea.value;
    }
});

function resetToDefault() {
    const textarea = document.getElementById('robots_content');
    const previewContent = document.getElementById('robots-preview-content');
    const defaultContent = `User-agent: *
Allow: /

Sitemap: {{ url('sitemap.xml') }}`;
    
    textarea.value = defaultContent;
    previewContent.textContent = defaultContent;
}

function loadTemplate(type) {
    const textarea = document.getElementById('robots_content');
    const previewContent = document.getElementById('robots-preview-content');
    const sitemapUrl = '{{ url('sitemap.xml') }}';
    
    let content = '';
    
    switch(type) {
        case 'default':
            content = `User-agent: *
Allow: /

Sitemap: ${sitemapUrl}`;
            break;
            
        case 'restrictive':
            content = `User-agent: *
Disallow: /admin/
Disallow: /private/
Disallow: /tmp/
Disallow: /api/
Disallow: /*.json$
Disallow: /*.xml$
Allow: /

Sitemap: ${sitemapUrl}`;
            break;
            
        case 'blog':
            content = `User-agent: *
Disallow: /admin/
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /wp-content/plugins/
Disallow: /wp-content/themes/
Disallow: /search/
Disallow: /author/
Disallow: /tag/
Allow: /wp-content/uploads/
Allow: /

Sitemap: ${sitemapUrl}`;
            break;
            
        case 'ecommerce':
            content = `User-agent: *
Disallow: /admin/
Disallow: /cart/
Disallow: /checkout/
Disallow: /account/
Disallow: /search/
Disallow: /filter/
Disallow: /sort/
Disallow: /*.php$
Allow: /

Sitemap: ${sitemapUrl}`;
            break;
    }
    
    textarea.value = content;
    previewContent.textContent = content;
}

// Add syntax highlighting for robots.txt
function highlightRobotsTxt() {
    const previewContent = document.getElementById('robots-preview-content');
    if (previewContent) {
        let content = previewContent.textContent;
        
        // Basic syntax highlighting
        content = content.replace(/^(User-agent:|Disallow:|Allow:|Sitemap:|Crawl-delay:)/gm, '<span style="color: #d73a49; font-weight: bold;">$1</span>');
        content = content.replace(/(\*|\/[^\s]*)/g, '<span style="color: #032f62;">$1</span>');
        
        previewContent.innerHTML = content;
    }
}

// Apply syntax highlighting on load and when content changes
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('robots_content');
    const previewContent = document.getElementById('robots-preview-content');
    
    textarea.addEventListener('input', function() {
        previewContent.textContent = this.value;
        setTimeout(highlightRobotsTxt, 10);
    });
    
    // Initial highlighting
    setTimeout(highlightRobotsTxt, 100);
});
</script>
@endpush
