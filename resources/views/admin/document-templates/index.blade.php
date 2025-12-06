@extends('admin.layouts.app')

@section('title', 'Document Templates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Document Templates</li>
@endsection

@push('styles')
<style>
    .template-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
    }
    .template-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .template-card-header {
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border-bottom: 1px solid #eee;
    }
    .template-card-body {
        padding: 1.25rem;
    }
    .template-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .template-icon.letter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .template-icon.form {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    .template-stats {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }
    .template-stat {
        text-align: center;
        flex: 1;
    }
    .template-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #333;
    }
    .template-stat-label {
        font-size: 0.75rem;
        color: #666;
        text-transform: uppercase;
    }
    .view-toggle {
        border-radius: 8px;
        overflow: hidden;
    }
    .view-toggle .btn {
        border-radius: 0;
        padding: 0.5rem 1rem;
    }
    .filter-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-icon.total { background: rgba(102, 126, 234, 0.15); color: #667eea; }
    .stat-icon.letters { background: rgba(118, 75, 162, 0.15); color: #764ba2; }
    .stat-icon.forms { background: rgba(17, 153, 142, 0.15); color: #11998e; }
    .stat-icon.active { background: rgba(40, 167, 69, 0.15); color: #28a745; }
    .action-dropdown .dropdown-menu {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border: none;
        padding: 0.5rem;
    }
    .action-dropdown .dropdown-item {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        margin-bottom: 2px;
    }
    .action-dropdown .dropdown-item:hover {
        background: #f8f9fa;
    }
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    .empty-state-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 3rem;
        color: #999;
    }
    .badge-usage {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt me-2 text-primary"></i>Document Templates
            </h1>
            <p class="text-muted mb-0">Create and manage letter and form templates for patient documents</p>
        </div>
        <div class="d-flex gap-2">
            @can('create', \App\Models\DocumentTemplate::class)
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-2"></i>Create Template
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.document-templates.create') }}?type=letter">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Letter Template
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.document-templates.create') }}?type=form">
                            <i class="fas fa-list-alt me-2 text-success"></i>Form Template
                        </a>
                    </li>
                </ul>
            </div>
            @endcan
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="h4 mb-0">{{ $templates->total() }}</div>
                <div class="text-muted small">Total Templates</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon letters">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div>
                <div class="h4 mb-0">{{ \App\Models\DocumentTemplate::where('type', 'letter')->count() }}</div>
                <div class="text-muted small">Letter Templates</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon forms">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <div class="h4 mb-0">{{ \App\Models\DocumentTemplate::where('type', 'form')->count() }}</div>
                <div class="text-muted small">Form Templates</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="h4 mb-0">{{ \App\Models\DocumentTemplate::where('is_active', true)->count() }}</div>
                <div class="text-muted small">Active Templates</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.document-templates.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-search me-1 text-muted"></i>Search
                        </label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search by name or slug..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-tag me-1 text-muted"></i>Type
                        </label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="letter" {{ request('type') == 'letter' ? 'selected' : '' }}>
                                <i class="fas fa-file-alt"></i> Letter
                            </option>
                            <option value="form" {{ request('type') == 'form' ? 'selected' : '' }}>
                                <i class="fas fa-list"></i> Form
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-toggle-on me-1 text-muted"></i>Status
                        </label>
                        <select name="is_active" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request()->anyFilled(['search', 'type', 'is_active']))
                            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- View Toggle & Results Count -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">
            <strong>{{ $templates->total() }}</strong> template(s) found
        </div>
        <div class="btn-group view-toggle" role="group">
            <button type="button" class="btn btn-sm {{ !request('view') || request('view') == 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}" onclick="setView('grid')">
                <i class="fas fa-th-large"></i>
            </button>
            <button type="button" class="btn btn-sm {{ request('view') == 'table' ? 'btn-primary' : 'btn-outline-secondary' }}" onclick="setView('table')">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    @if($templates->count() > 0)
        <!-- Grid View -->
        <div id="gridView" style="{{ request('view') == 'table' ? 'display:none' : '' }}">
            <div class="row g-4">
                @foreach($templates as $template)
                <div class="col-lg-4 col-md-6">
                    <div class="template-card">
                        <div class="template-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="template-icon {{ $template->type }}">
                                        <i class="fas fa-{{ $template->type === 'letter' ? 'envelope-open-text' : 'clipboard-list' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ $template->name }}</h6>
                                        <code class="small text-muted">{{ $template->slug }}</code>
                                    </div>
                                </div>
                                <div class="dropdown action-dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.document-templates.show', $template) }}">
                                                <i class="fas fa-eye me-2 text-primary"></i>View
                                            </a>
                                        </li>
                                        @can('update', $template)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.document-templates.edit', $template) }}">
                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                            </a>
                                        </li>
                                        @endcan
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.document-templates.clone', $template) }}">
                                                <i class="fas fa-copy me-2 text-info"></i>Clone
                                            </a>
                                        </li>
                                        @can('deactivate', $template)
                                        @if($template->is_active)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.document-templates.deactivate', $template) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Deactivate this template?')">
                                                    <i class="fas fa-toggle-off me-2"></i>Deactivate
                                                </button>
                                            </form>
                                        </li>
                                        @else
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.document-templates.activate', $template) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-toggle-on me-2"></i>Activate
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @endcan
                                        @can('delete', $template)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.document-templates.destroy', $template) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Delete this template? This cannot be undone.')">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="template-card-body">
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-{{ $template->type === 'letter' ? 'primary' : 'success' }}">
                                    {{ ucfirst($template->type) }}
                                </span>
                                @if($template->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </div>

                            <div class="small text-muted mb-2">
                                <i class="fas fa-user me-1"></i>
                                Created by {{ $template->creator->name ?? 'Unknown' }}
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $template->created_at->format('M d, Y') }}
                            </div>

                            <div class="template-stats">
                                <div class="template-stat">
                                    <div class="template-stat-value">{{ $template->patientDocuments()->count() }}</div>
                                    <div class="template-stat-label">Documents</div>
                                </div>
                                <div class="template-stat">
                                    <div class="template-stat-value">{{ $template->patientDocuments()->where('status', 'final')->count() }}</div>
                                    <div class="template-stat-label">Finalized</div>
                                </div>
                                <div class="template-stat">
                                    <div class="template-stat-value">
                                        {{ $template->patientDocuments()->whereHas('deliveries', function($q) { $q->where('status', 'sent'); })->count() }}
                                    </div>
                                    <div class="template-stat-label">Sent</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Table View -->
        <div id="tableView" style="{{ request('view') != 'table' ? 'display:none' : '' }}">
            <div class="card" style="border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="border-top-left-radius: 16px;">Template</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Usage</th>
                                    <th>Created</th>
                                    <th style="border-top-right-radius: 16px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="template-icon {{ $template->type }}" style="width: 40px; height: 40px; font-size: 1rem;">
                                                <i class="fas fa-{{ $template->type === 'letter' ? 'envelope-open-text' : 'clipboard-list' }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $template->name }}</div>
                                                <code class="small text-muted">{{ $template->slug }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $template->type === 'letter' ? 'primary' : 'success' }}">
                                            {{ ucfirst($template->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark badge-usage">
                                            {{ $template->patientDocuments()->count() }} docs
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">{{ $template->created_at->format('M d, Y') }}</div>
                                        <div class="small text-muted">{{ $template->creator->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.document-templates.show', $template) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $template)
                                            <a href="{{ route('admin.document-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            <a href="{{ route('admin.document-templates.clone', $template) }}" class="btn btn-sm btn-outline-info" title="Clone">
                                                <i class="fas fa-copy"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }}
                of {{ $templates->total() }} templates
            </div>
            {{ $templates->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="card" style="border-radius: 16px; border: none;">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h4 class="mb-2">No Templates Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['search', 'type', 'is_active']))
                            No templates match your search criteria. Try adjusting your filters.
                        @else
                            Get started by creating your first document template.
                        @endif
                    </p>
                    @can('create', \App\Models\DocumentTemplate::class)
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('admin.document-templates.create') }}?type=letter" class="btn btn-primary">
                            <i class="fas fa-file-alt me-2"></i>Create Letter Template
                        </a>
                        <a href="{{ route('admin.document-templates.create') }}?type=form" class="btn btn-success">
                            <i class="fas fa-list-alt me-2"></i>Create Form Template
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function setView(view) {
    const url = new URL(window.location.href);
    url.searchParams.set('view', view);
    window.location.href = url.toString();
}

$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);

    // Debounced search
    let searchTimeout;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });
});
</script>
@endpush
