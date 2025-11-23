@extends('admin.layouts.app')

@section('title', 'Homepage Features')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Homepage Features</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="page-title mb-1">Homepage Features</h4>
                <p class="text-muted mb-0">Manage the features displayed on your homepage</p>
            </div>
            <a href="{{ contextRoute('homepage-features.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Feature
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Icon</th>
                                <th width="25%">Title</th>
                                <th width="40%">Description</th>
                                <th width="10%">Status</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($features as $feature)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="text-center">
                                            <i class="{{ $feature->icon }}" style="color: {{ $feature->color }}; font-size: 24px;"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $feature->title }}</div>
                                        @if($feature->subtitle)
                                            <div class="text-muted small">{{ $feature->subtitle }}</div>
                                        @endif
                                        <small class="text-muted">Sort Order: {{ $feature->sort_order }}</small>
                                    </td>
                                    <td>
                                        <p class="mb-0">{{ Str::limit($feature->description, 100) }}</p>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" 
                                                   type="checkbox" 
                                                   data-id="{{ $feature->id }}"
                                                   data-url="{{ contextRoute('homepage-features.toggle-status', $feature) }}"
                                                   {{ $feature->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label small">
                                                {{ $feature->is_active ? 'Active' : 'Inactive' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ contextRoute('homepage-features.show', $feature) }}" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ contextRoute('homepage-features.edit', $feature) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ contextRoute('homepage-features.destroy', $feature) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                                        title="Delete"
                                                        data-confirm="Are you sure you want to delete this feature?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <p class="mb-0">No homepage features found.</p>
                                            <p class="mb-0">
                                                <a href="{{ contextRoute('homepage-features.create') }}" class="text-primary">
                                                    Add your first feature
                                                </a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($features->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $features->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Status toggle
    $('.status-toggle').change(function() {
        const toggle = $(this);
        const url = toggle.data('url');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const label = toggle.siblings('label');
                    label.text(response.is_active ? 'Active' : 'Inactive');
                    
                    // Show success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function() {
                toggle.prop('checked', !toggle.prop('checked'));
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update feature status.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // Delete confirmation
    $('.delete-btn').click(function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const confirmText = $(this).data('confirm');
        
        Swal.fire({
            title: 'Are you sure?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
