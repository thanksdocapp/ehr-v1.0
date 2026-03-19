@extends('admin.layouts.app')

@section('title', 'Letter & Form Templates')

@section('breadcrumb')
    <li class="breadcrumb-item active">Templates</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Letter & Form Templates</h1>
            <p class="text-muted mb-0">Manage your document templates for letters and forms</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.templates.create', ['type' => 'letter']) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Letter
            </a>
            <a href="{{ route('admin.templates.create', ['type' => 'form']) }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>New Form
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.templates.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search templates..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="letter" {{ request('type') === 'letter' ? 'selected' : '' }}>Letters</option>
                        <option value="form" {{ request('type') === 'form' ? 'selected' : '' }}>Forms</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ownership</label>
                    <select name="ownership" class="form-select">
                        <option value="">All Templates</option>
                        <option value="own" {{ request('ownership') === 'own' ? 'selected' : '' }}>My Templates</option>
                        <option value="system" {{ request('ownership') === 'system' ? 'selected' : '' }}>System Templates</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Templates Grid -->
    @if($templates->count() > 0)
        <div class="row">
            @foreach($templates as $template)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge {{ $template->type_badge_class }} me-2">
                                    <i class="fas {{ $template->type_icon }} me-1"></i>
                                    {{ ucfirst($template->type) }}
                                </span>
                                @if($template->is_system)
                                    <span class="badge bg-info" title="System template visible to all">
                                        <i class="fas fa-globe"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.templates.show', $template) }}">
                                            <i class="fas fa-eye me-2"></i>View
                                        </a>
                                    </li>
                                    @can('update', $template)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.templates.edit', $template) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                    @endcan
                                    @can('duplicate', $template)
                                        <li>
                                            <form action="{{ route('admin.templates.duplicate', $template) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-copy me-2"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                    @can('toggleActive', $template)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.templates.toggle-active', $template) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item">
                                                    @if($template->is_active)
                                                        <i class="fas fa-toggle-off me-2"></i>Deactivate
                                                    @else
                                                        <i class="fas fa-toggle-on me-2"></i>Activate
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                    @can('delete', $template)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="{{ route('admin.templates.show', $template) }}" class="text-decoration-none text-dark">
                                    {{ $template->name }}
                                </a>
                            </h5>
                            @if($template->description)
                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($template->description, 100) }}
                                </p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-file-alt me-1"></i>
                                    {{ $template->usage_count }} uses
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    By {{ $template->creator->name ?? 'Unknown' }}
                                </small>
                                <small class="text-muted">
                                    {{ $template->updated_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $templates->links() }}
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5>No templates found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'type', 'status', 'ownership']))
                        Try adjusting your filters or search criteria.
                    @else
                        Get started by creating your first template.
                    @endif
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.templates.create', ['type' => 'letter']) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Letter Template
                    </a>
                    <a href="{{ route('admin.templates.create', ['type' => 'form']) }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Create Form Template
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
