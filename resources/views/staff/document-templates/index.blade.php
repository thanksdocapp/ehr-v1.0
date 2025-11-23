@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Document Templates')
@section('page-title', 'Document Templates')
@section('page-subtitle', 'Browse letter and form templates for patient documents')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-file-alt me-2 text-primary"></i>Document Templates
                    </h1>
                    <p class="text-muted mb-0">Browse letter and form templates for patient documents</p>
                </div>
                @can('create', \App\Models\DocumentTemplate::class)
                <a href="{{ route('staff.document-templates.create') }}" class="btn btn-doctor-primary">
                    <i class="fas fa-plus me-2"></i>Create Template
                </a>
                @endcan
            </div>
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

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.document-templates.index') }}" id="filterForm">
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-doctor-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
                @if(request()->anyFilled(['search', 'type']))
                <div class="mt-3">
                    <a href="{{ route('staff.document-templates.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('staff.document-templates.show', $template) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $template)
                                            <a href="{{ route('staff.document-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
                    <p class="text-muted mb-4">There are no active document templates available.</p>
                    @can('create', \App\Models\DocumentTemplate::class)
                        <a href="{{ route('staff.document-templates.create') }}" class="btn btn-doctor-primary">
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

