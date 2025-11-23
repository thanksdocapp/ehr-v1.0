@extends('admin.layouts.app')

@section('title', 'SEO Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">SEO Management</li>
@endsection

@section('content')
<div class="page-title">
    <h1>SEO Management</h1>
    <p class="page-subtitle">Optimize your website's search engine visibility</p>
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

<!-- SEO Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Total Pages</div>
                        <div class="stat-value">{{ $stats['total_pages'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Optimized</div>
                        <div class="stat-value">{{ $stats['optimized_pages'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Needs Work</div>
                        <div class="stat-value">{{ $stats['pending_pages'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Avg Score</div>
                        <div class="stat-value">{{ $stats['avg_seo_score'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEO Configuration -->
<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">SEO Configuration</h3>
            </div>
            <div class="card-body">
                <form action="{{ contextRoute('seo.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meta_keywords" class="form-label">Default Keywords</label>
                                <textarea class="form-control @error('meta_keywords') is-invalid @enderror" 
                                          id="meta_keywords" name="meta_keywords" rows="3" 
                                          placeholder="hospital, medical care, healthcare">{{ old('meta_keywords', $seoConfig->meta_keywords ?? '') }}</textarea>
                                <small class="form-text text-muted">Separate keywords with commas</small>
                                @error('meta_keywords')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Default Description</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3" 
                                          placeholder="Your trusted healthcare partner providing quality medical services">{{ old('meta_description', $seoConfig->meta_description ?? '') }}</textarea>
                                <small class="form-text text-muted">Maximum 160 characters</small>
                                @error('meta_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="social_title" class="form-label">Social Media Title</label>
                                <input type="text" class="form-control @error('social_title') is-invalid @enderror" 
                                       id="social_title" name="social_title" 
                                       value="{{ old('social_title', $seoConfig->social_title ?? '') }}" 
                                       placeholder="ThanksDoc EHR - Quality Healthcare">
                                @error('social_title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="social_description" class="form-label">Social Media Description</label>
                                <textarea class="form-control @error('social_description') is-invalid @enderror" 
                                          id="social_description" name="social_description" rows="2" 
                                          placeholder="Comprehensive healthcare services with experienced medical professionals">{{ old('social_description', $seoConfig->social_description ?? '') }}</textarea>
                                @error('social_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- SEO Pages List -->
        <div class="admin-card mt-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">SEO Pages</h3>
                    <a href="{{ contextRoute('seo.pages.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Add New Page
                    </a>
                </div>
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
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
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
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $page->seo_score >= 80 ? 'success' : ($page->seo_score >= 60 ? 'warning' : 'danger') }}" 
                                             role="progressbar" style="width: {{ $page->seo_score }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $page->seo_score }}%</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $page->status == 'optimized' ? 'success' : ($page->status == 'needs_work' ? 'warning' : 'danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $page->status)) }}
                                    </span>
                                </td>
                                <td>{{ formatDate($page->updated_at) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ contextRoute('seo.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteSeoPage({{ $page->id }}); return false;" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                {{ $pages->links() }}
                @else
                <div class="text-center py-4">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No SEO pages found</h5>
                    <p class="text-muted">Start by adding your first SEO page to track and optimize.</p>
                    <a href="{{ contextRoute('seo.pages.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add First Page
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('seo.meta-tags') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>Meta Tags Settings
                    </a>
                    <a href="{{ contextRoute('seo.sitemap') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sitemap me-2"></i>Sitemap Management
                    </a>
                    <a href="{{ contextRoute('seo.robots') }}" class="btn btn-outline-primary">
                        <i class="fas fa-robot me-2"></i>Robots.txt
                    </a>
                    <a href="{{ contextRoute('seo.analytics') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar me-2"></i>SEO Analytics
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Social Image Upload -->
        <div class="admin-card mt-4">
            <div class="card-header">
                <h3 class="card-title mb-0">Social Media Image</h3>
            </div>
            <div class="card-body">
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    @if($seoConfig->social_image ?? false)
                    <div class="mb-3 text-center">
                        <img src="{{ asset('storage/' . $seoConfig->social_image) }}" 
                             alt="Current social image" class="img-thumbnail" style="max-width: 200px;">
                        <p class="text-muted small mt-2">Current social media image</p>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="social_image" class="form-label">Upload New Image</label>
                        <input type="file" class="form-control @error('social_image') is-invalid @enderror" 
                               id="social_image" name="social_image" accept="image/*">
                        <small class="form-text text-muted">Recommended: 1200x630px, Max: 5MB</small>
                        @error('social_image')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload me-2"></i>Upload Image
                    </button>
                </form>
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
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
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
    
    .btn-outline-primary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
    
    .btn-group .btn {
        border-radius: 6px;
    }
    
    .btn-group .btn:not(:last-child) {
        margin-right: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
function deleteSeoPage(pageId) {
    ModalSystem.confirm({
        title: 'Delete SEO Page',
        message: 'Are you sure you want to delete this SEO page?',
        confirmText: 'Delete',
        confirmClass: 'btn-danger',
        icon: 'fas fa-exclamation-triangle',
        onConfirm: function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/seo/pages/${pageId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
