@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Letters & Forms Templates')
@section('page-title', 'Letters & Forms Templates')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-file-medical me-2 text-primary"></i>Letters & Forms Templates
                    </h1>
                    <p class="text-muted mb-0">NHS/CQC compliant document templates</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.generated-documents.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf me-2"></i>My Documents
                    </a>
                    @can('create', \App\Models\Template::class)
                    <a href="{{ route('staff.templates.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Create Template
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.templates.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search Templates</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="letter" {{ request('type') == 'letter' ? 'selected' : '' }}>Letters</option>
                            <option value="form" {{ request('type') == 'form' ? 'selected' : '' }}>Forms</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-doctor-primary w-100">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    @if(request()->anyFilled(['search', 'type']))
                    <div class="col-md-2">
                        <a href="{{ route('staff.templates.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row">
        @forelse($templates as $template)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="doctor-card h-100">
                <div class="doctor-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-{{ $template->type === 'letter' ? 'primary' : 'info' }}">
                            <i class="fas fa-{{ $template->type === 'letter' ? 'envelope' : 'clipboard-list' }} me-1"></i>
                            {{ ucfirst($template->type) }}
                        </span>
                        @if($template->is_system)
                            <span class="badge bg-secondary">System</span>
                        @endif
                    </div>

                    <h5 class="card-title mb-2">{{ $template->name }}</h5>
                    <p class="text-muted small mb-3">
                        Created {{ $template->created_at->diffForHumans() }}
                        @if($template->creator)
                            by {{ $template->creator->name }}
                        @endif
                    </p>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('staff.generated-documents.create', ['template_id' => $template->id]) }}"
                           class="btn btn-sm btn-success flex-fill">
                            <i class="fas fa-file-pdf me-1"></i>Generate
                        </a>
                        <a href="{{ route('staff.templates.show', $template) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $template)
                        <a href="{{ route('staff.templates.edit', $template) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No templates found</h5>
                <p class="text-muted">No templates match your search criteria.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $templates->links() }}
    </div>
    @endif
</div>
@endsection
