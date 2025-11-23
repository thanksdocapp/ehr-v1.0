@extends('admin.layouts.app')

@section('title', 'Document Templates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Document Templates</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt me-2 text-primary"></i>Document Templates
            </h1>
            <p class="text-muted mb-0">Manage letter and form templates for patient documents</p>
        </div>
        @can('create', \App\Models\DocumentTemplate::class)
        <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Template
        </a>
        @endcan
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

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('admin.document-templates.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search templates..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="letter" {{ request('type') == 'letter' ? 'selected' : '' }}>Letter</option>
                            <option value="form" {{ request('type') == 'form' ? 'selected' : '' }}>Form</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="">All Status</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
                @if(request()->anyFilled(['search', 'type', 'is_active']))
                <div class="mt-3">
                    <a href="{{ route('admin.document-templates.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-list me-2"></i>Templates ({{ $templates->total() }})
            </h5>
        </div>
        <div class="doctor-card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $template->name }}</div>
                                    <small class="text-muted">{{ $template->slug }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $template->type === 'letter' ? 'primary' : 'info' }}">
                                        <i class="fas fa-{{ $template->type === 'letter' ? 'file-alt' : 'list' }} me-1"></i>
                                        {{ ucfirst($template->type) }}
                                    </span>
                                </td>
                                <td>
                                    <code class="text-muted">{{ $template->slug }}</code>
                                </td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->creator)
                                        {{ $template->creator->name }}
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $template->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $template->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.document-templates.show', $template) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $template)
                                            <a href="{{ route('admin.document-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('deactivate', $template)
                                        @if($template->is_active)
                                            <form action="{{ route('admin.document-templates.deactivate', $template) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to deactivate this template?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Deactivate">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @endcan
                                        @can('delete', $template)
                                            <form action="{{ route('admin.document-templates.destroy', $template) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No templates found</h5>
                    <p class="text-muted mb-4">There are no document templates yet.</p>
                    @can('create', \App\Models\DocumentTemplate::class)
                        <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Template
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
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

