@extends('admin.layouts.app')

@section('title', 'Homepage Sections')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Homepage Sections</h1>
        <a href="{{ contextRoute('homepage-sections.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Section
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            @if($sections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Section Name</th>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sections as $section)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $section->section_name }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $section->title }}</strong>
                                    </td>
                                    <td>
                                        @if($section->subtitle)
                                            <small class="text-muted">{{ Str::limit($section->subtitle, 50) }}</small>
                                        @else
                                            <small class="text-muted">No subtitle</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($section->description)
                                            <small class="text-muted">{{ Str::limit($section->description, 60) }}</small>
                                        @else
                                            <small class="text-muted">No description</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($section->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $section->sort_order }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ contextRoute('homepage-sections.edit', $section) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ contextRoute('homepage-sections.show', $section) }}" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $section->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <form id="delete-form-{{ $section->id }}" 
                                              action="{{ contextRoute('homepage-sections.destroy', $section) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No homepage sections found</h5>
                    <p class="text-muted">Get started by creating your first homepage section.</p>
                    <a href="{{ contextRoute('homepage-sections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add First Section
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this homepage section? This action cannot be undone.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endsection
