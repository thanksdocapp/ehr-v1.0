@extends('admin.layouts.app')

@section('title', 'SEO Analytics - ThanksDoc EHR')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('seo.index') }}">SEO Management</a></li>
    <li class="breadcrumb-item active">SEO Analytics</li>
@endsection

@section('content')
<div class="page-title">
    <h1>SEO Analytics</h1>
    <p class="page-subtitle">Monitor and analyze ThanksDoc EHR's website SEO performance</p>
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

<!-- SEO Score Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Excellent Pages</div>
                        <div class="stat-value">{{ $analytics['score_distribution']['excellent'] ?? 0 }}</div>
                        <div class="stat-change text-success">
                            <small>90%+ SEO Score</small>
                        </div>
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
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Good Pages</div>
                        <div class="stat-value">{{ $analytics['score_distribution']['good'] ?? 0 }}</div>
                        <div class="stat-change text-primary">
                            <small>80-89% SEO Score</small>
                        </div>
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
                        <div class="stat-label">Fair Pages</div>
                        <div class="stat-value">{{ $analytics['score_distribution']['fair'] ?? 0 }}</div>
                        <div class="stat-change text-warning">
                            <small>60-79% SEO Score</small>
                        </div>
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
                        <div class="stat-icon bg-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="stat-label">Poor Pages</div>
                        <div class="stat-value">{{ $analytics['score_distribution']['poor'] ?? 0 }}</div>
                        <div class="stat-change text-danger">
                            <small>Below 60% SEO Score</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- SEO Performance Over Time -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">SEO Performance Trend (Last 30 Days)</h3>
            </div>
            <div class="card-body">
                <canvas id="seoTrendChart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Top Performing Pages -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Top Performing Pages</h3>
            </div>
            <div class="card-body">
                @if(isset($analytics['top_pages']) && $analytics['top_pages']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Page Title</th>
                                <th>URL</th>
                                <th>SEO Score</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analytics['top_pages'] as $page)
                            <tr>
                                <td>
                                    <strong>{{ $page->title }}</strong>
                                    @if($page->meta_title)
                                    <br><small class="text-muted">{{ $page->meta_title }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $page->url }}" target="_blank" class="text-decoration-none">
                                        {{ Str::limit($page->url, 40) }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="height: 8px; width: 60px;">
                                            <div class="progress-bar bg-{{ $page->seo_score >= 90 ? 'success' : ($page->seo_score >= 80 ? 'primary' : ($page->seo_score >= 60 ? 'warning' : 'danger')) }}" 
                                                 role="progressbar" style="width: {{ $page->seo_score }}%"></div>
                                        </div>
                                        <span class="text-muted">{{ $page->seo_score }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $page->status == 'optimized' ? 'success' : ($page->status == 'needs_work' ? 'warning' : 'danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $page->status)) }}
                                    </span>
                                </td>
                                <td>{{ formatDate($page->updated_at) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No SEO Pages Found</h5>
                    <p class="text-muted">Add some SEO pages to see performance analytics.</p>
                    <a href="{{ contextRoute('seo.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add SEO Pages
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Pages Needing Attention -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Pages Needing Attention</h3>
            </div>
            <div class="card-body">
                @if(isset($analytics['attention_pages']) && $analytics['attention_pages']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Page Title</th>
                                <th>URL</th>
                                <th>SEO Score</th>
                                <th>Issues</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analytics['attention_pages'] as $page)
                            <tr>
                                <td>
                                    <strong>{{ $page->title }}</strong>
                                    @if($page->meta_title)
                                    <br><small class="text-muted">{{ $page->meta_title }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $page->url }}" target="_blank" class="text-decoration-none">
                                        {{ Str::limit($page->url, 40) }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="height: 8px; width: 60px;">
                                            <div class="progress-bar bg-danger" 
                                                 role="progressbar" style="width: {{ $page->seo_score }}%"></div>
                                        </div>
                                        <span class="text-danger">{{ $page->seo_score }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="issues-list">
                                        @if(!$page->meta_title || strlen($page->meta_title) < 10)
                                        <span class="badge bg-warning me-1">Missing Meta Title</span>
                                        @endif
                                        @if(!$page->meta_description || strlen($page->meta_description) < 120)
                                        <span class="badge bg-warning me-1">Poor Meta Description</span>
                                        @endif
                                        @if(!$page->meta_keywords)
                                        <span class="badge bg-info me-1">No Keywords</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Optimize
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-success">All Pages Are Optimized!</h5>
                    <p class="text-muted">Great job! All your pages have good SEO scores.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- SEO Score Distribution -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Score Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="scoreDistributionChart" height="250"></canvas>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('seo.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-cog me-2"></i>SEO Settings
                    </a>
                    <a href="{{ contextRoute('seo.meta-tags') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>Meta Tags
                    </a>
                    <a href="{{ contextRoute('seo.sitemap') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sitemap me-2"></i>Sitemap
                    </a>
                    <a href="{{ contextRoute('seo.robots') }}" class="btn btn-outline-primary">
                        <i class="fas fa-robot me-2"></i>Robots.txt
                    </a>
                </div>
            </div>
        </div>
        
        <!-- SEO Recommendations -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">SEO Recommendations</h3>
            </div>
            <div class="card-body">
                <div class="recommendations-list">
                    @php
                    $totalPages = ($analytics['score_distribution']['excellent'] ?? 0) + 
                                  ($analytics['score_distribution']['good'] ?? 0) + 
                                  ($analytics['score_distribution']['fair'] ?? 0) + 
                                  ($analytics['score_distribution']['poor'] ?? 0);
                    $poorPages = $analytics['score_distribution']['poor'] ?? 0;
                    $fairPages = $analytics['score_distribution']['fair'] ?? 0;
                    @endphp
                    
                    @if($poorPages > 0)
                    <div class="recommendation-item">
                        <div class="recommendation-icon">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                        </div>
                        <div class="recommendation-content">
                            <h6>Optimize Poor Performing Pages</h6>
                            <p class="text-muted">{{ $poorPages }} pages need immediate attention to improve SEO scores.</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($fairPages > 0)
                    <div class="recommendation-item">
                        <div class="recommendation-icon">
                            <i class="fas fa-chart-line text-warning"></i>
                        </div>
                        <div class="recommendation-content">
                            <h6>Improve Fair Pages</h6>
                            <p class="text-muted">{{ $fairPages }} pages could benefit from SEO improvements.</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="recommendation-item">
                        <div class="recommendation-icon">
                            <i class="fas fa-sync-alt text-info"></i>
                        </div>
                        <div class="recommendation-content">
                            <h6>Update Sitemap</h6>
                            <p class="text-muted">Regularly update your sitemap to help search engines index your content.</p>
                        </div>
                    </div>
                    
                    <div class="recommendation-item">
                        <div class="recommendation-icon">
                            <i class="fas fa-search text-primary"></i>
                        </div>
                        <div class="recommendation-content">
                            <h6>Monitor Search Console</h6>
                            <p class="text-muted">Check Google Search Console for indexing issues and opportunities.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- External Tools -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">External SEO Tools</h3>
            </div>
            <div class="card-body">
                <div class="external-tools">
                    <a href="https://search.google.com/search-console" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                        <i class="fab fa-google me-2"></i>Google Search Console
                    </a>
                    <a href="https://analytics.google.com" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                        <i class="fab fa-google me-2"></i>Google Analytics
                    </a>
                    <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                        <i class="fab fa-microsoft me-2"></i>Bing Webmaster Tools
                    </a>
                    <a href="https://pagespeed.web.dev" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                        <i class="fas fa-tachometer-alt me-2"></i>PageSpeed Insights
                    </a>
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
    
    .stat-change {
        font-size: 0.75rem;
        font-weight: 500;
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
    
    .issues-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .recommendations-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .recommendation-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
    }
    
    .recommendation-item:last-child {
        margin-bottom: 0;
    }
    
    .recommendation-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .recommendation-content h6 {
        margin-bottom: 0.5rem;
        color: #495057;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .recommendation-content p {
        margin-bottom: 0;
        font-size: 0.875rem;
        line-height: 1.4;
    }
    
    .external-tools .btn {
        text-align: left;
        padding: 12px 16px;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
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
    
    .btn-outline-secondary {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .card-header h3 {
        color: #495057;
        font-weight: 600;
    }
    
    canvas {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SEO Trend Chart
    const trendCtx = document.getElementById('seoTrendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [
                @if(isset($analytics['daily_scores']))
                    @foreach($analytics['daily_scores'] as $score)
                        '{{ date('M j', strtotime($score->date)) }}',
                    @endforeach
                @else
                    @for($i = 29; $i >= 0; $i--)
                        '{{ date('M j', strtotime('-' . $i . ' days')) }}',
                    @endfor
                @endif
            ],
            datasets: [{
                label: 'Average SEO Score',
                data: [
                    @if(isset($analytics['daily_scores']))
                        @foreach($analytics['daily_scores'] as $score)
                            {{ round($score->avg_score) }},
                        @endforeach
                    @else
                        @for($i = 29; $i >= 0; $i--)
                            {{ rand(60, 85) }},
                        @endfor
                    @endif
                ],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Score Distribution Chart
    const distributionCtx = document.getElementById('scoreDistributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Excellent (90%+)', 'Good (80-89%)', 'Fair (60-79%)', 'Poor (<60%)'],
            datasets: [{
                data: [
                    {{ $analytics['score_distribution']['excellent'] ?? 0 }},
                    {{ $analytics['score_distribution']['good'] ?? 0 }},
                    {{ $analytics['score_distribution']['fair'] ?? 0 }},
                    {{ $analytics['score_distribution']['poor'] ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#667eea',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush
