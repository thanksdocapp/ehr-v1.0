@extends('admin.layouts.app')

@section('title', 'Sitemap Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('seo.index') }}">SEO Management</a></li>
    <li class="breadcrumb-item active">Sitemap Management</li>
@endsection

@section('content')
<div class="page-title">
    <h1>Sitemap Management</h1>
    <p class="page-subtitle">Generate and manage XML sitemaps for search engines</p>
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
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Current Sitemap Status</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="sitemap-status">
                            <div class="status-icon">
                                @if($sitemaps['exists'])
                                    <i class="fas fa-check-circle text-success fa-3x"></i>
                                @else
                                    <i class="fas fa-exclamation-triangle text-warning fa-3x"></i>
                                @endif
                            </div>
                            <div class="status-content">
                                <h5>{{ $sitemaps['exists'] ? 'Sitemap Active' : 'No Sitemap Found' }}</h5>
                                <p class="text-muted">
                                    @if($sitemaps['exists'])
                                        Your sitemap is available and ready for search engines.
                                    @else
                                        Generate a sitemap to help search engines index your site.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sitemap-info">
                            <div class="info-item">
                                <strong>File Status:</strong>
                                <span class="badge bg-{{ $sitemaps['exists'] ? 'success' : 'warning' }}">
                                    {{ $sitemaps['exists'] ? 'Generated' : 'Missing' }}
                                </span>
                            </div>
                            @if($sitemaps['exists'])
                            <div class="info-item">
                                <strong>URLs Count:</strong>
                                <span>{{ $sitemaps['url_count'] }} pages</span>
                            </div>
                            <div class="info-item">
                                <strong>File Size:</strong>
                                <span>{{ number_format($sitemaps['size'] / 1024, 2) }} KB</span>
                            </div>
                            <div class="info-item">
                                <strong>Last Modified:</strong>
                                <span>{{ $sitemaps['last_modified'] ? date('M d, Y H:i', $sitemaps['last_modified']) : 'Never' }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="sitemap-actions">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ contextRoute('seo.sitemap.generate') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    {{ $sitemaps['exists'] ? 'Regenerate Sitemap' : 'Generate Sitemap' }}
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            @if($sitemaps['exists'])
                                <a href="{{ contextRoute('seo.sitemap.download') }}" class="btn btn-outline-primary btn-lg w-100">
                                    <i class="fas fa-download me-2"></i>Download Sitemap
                                </a>
                            @else
                                <button class="btn btn-outline-secondary btn-lg w-100" disabled>
                                    <i class="fas fa-download me-2"></i>Download Sitemap
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Pages Included in Sitemap</h3>
            </div>
            <div class="card-body">
                @if($pages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Page Title</th>
                                <th>URL</th>
                                <th>SEO Score</th>
                                <th>Priority</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pages as $page)
                            <tr>
                                <td>
                                    <strong>{{ $page->title }}</strong>
                                    @if($page->meta_title)
                                    <br><small class="text-muted">{{ $page->meta_title }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $page->url }}" target="_blank" class="text-decoration-none">
                                        {{ $page->url }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px; width: 60px;">
                                        <div class="progress-bar bg-{{ $page->seo_score >= 80 ? 'success' : ($page->seo_score >= 60 ? 'warning' : 'danger') }}" 
                                             role="progressbar" style="width: {{ $page->seo_score }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $page->seo_score }}%</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $page->seo_score >= 80 ? 'success' : 'secondary' }}">
                                        {{ $page->seo_score >= 80 ? '1.0' : '0.8' }}
                                    </span>
                                </td>
                                <td>{{ formatDate($page->updated_at) }}</td>
                                <td>
                                    @if($page->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Active Pages Found</h5>
                    <p class="text-muted">Add some SEO pages to include them in your sitemap.</p>
                    <a href="{{ contextRoute('seo.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add SEO Pages
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Sitemap Information</h3>
            </div>
            <div class="card-body">
                <div class="sitemap-details">
                    <div class="detail-item">
                        <i class="fas fa-link text-primary me-2"></i>
                        <strong>Sitemap URL:</strong>
                        <div class="mt-1">
                            <a href="{{ url('sitemap.xml') }}" target="_blank" class="text-decoration-none">
                                {{ url('sitemap.xml') }}
                                <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-robot text-primary me-2"></i>
                        <strong>Robots.txt Reference:</strong>
                        <div class="mt-1">
                            <code>Sitemap: {{ url('sitemap.xml') }}</code>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <strong>Update Frequency:</strong>
                        <div class="mt-1">Weekly (recommended)</div>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        <strong>Priority System:</strong>
                        <div class="mt-1">
                            <small class="text-muted">
                                High SEO score pages (80%+) get priority 1.0<br>
                                Other pages get priority 0.8
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Search Engine Submission</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Submit your sitemap to search engines for better indexing:</p>
                
                <div class="search-engine-links">
                    <a href="https://search.google.com/search-console" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fab fa-google me-2"></i>Google Search Console
                    </a>
                    <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fab fa-microsoft me-2"></i>Bing Webmaster Tools
                    </a>
                    <a href="https://webmaster.yandex.com" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-search me-2"></i>Yandex Webmaster
                    </a>
                </div>
                
                <hr class="my-3">
                
                <div class="submission-tips">
                    <h6 class="text-primary">Quick Tips:</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1">
                            <i class="fas fa-check text-success me-1"></i>
                            Resubmit after major content changes
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-check text-success me-1"></i>
                            Monitor indexing status in Search Console
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-check text-success me-1"></i>
                            Update sitemap weekly or when adding new pages
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Sitemap Statistics</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $pages->count() }}</div>
                        <div class="stat-label">Total Pages</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $pages->where('is_active', true)->count() }}</div>
                        <div class="stat-label">Active Pages</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $pages->where('seo_score', '>=', 80)->count() }}</div>
                        <div class="stat-label">High Priority</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $pages->where('seo_score', '<', 80)->count() }}</div>
                        <div class="stat-label">Normal Priority</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .sitemap-status {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .status-icon {
        flex-shrink: 0;
    }
    
    .status-content h5 {
        margin-bottom: 0.5rem;
        color: #495057;
    }
    
    .sitemap-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        height: 100%;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .sitemap-actions {
        margin-top: 20px;
    }
    
    .sitemap-details {
        space-y: 20px;
    }
    
    .detail-item {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .detail-item strong {
        display: block;
        margin-bottom: 5px;
        color: #495057;
    }
    
    .search-engine-links .btn {
        text-align: left;
        padding: 12px 16px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .stat-item {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 600;
        color: #667eea;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
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
    
    .btn-lg {
        padding: 15px 30px;
        font-size: 1.1rem;
    }
    
    .btn-outline-primary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .progress {
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .card-header h3 {
        color: #495057;
        font-weight: 600;
    }
    
    .submission-tips h6 {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .submission-tips ul {
        margin-bottom: 0;
    }
    
    .submission-tips li {
        color: #6c757d;
    }
    
    code {
        background: #f8f9fa;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
        color: #e83e8c;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh sitemap info every 30 seconds when generating
    let isGenerating = false;
    
    const generateForm = document.querySelector('form[action*="generate"]');
    if (generateForm) {
        generateForm.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
            isGenerating = true;
        });
    }
    
    // Copy sitemap URL to clipboard
    const sitemapUrl = document.querySelector('a[href*="sitemap.xml"]');
    if (sitemapUrl) {
        sitemapUrl.addEventListener('click', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                navigator.clipboard.writeText(this.href).then(() => {
                    // Show temporary success message
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    setTimeout(() => {
                        this.textContent = originalText;
                    }, 2000);
                });
            }
        });
    }
    
    // Tooltips for external links
    const externalLinks = document.querySelectorAll('a[target="_blank"]');
    externalLinks.forEach(link => {
        link.setAttribute('title', 'Opens in new tab');
    });
});
</script>
@endpush
