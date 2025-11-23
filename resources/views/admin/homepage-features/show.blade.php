@extends('admin.layouts.app')

@section('title', 'View Feature')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('homepage-features.index') }}">Homepage Features</a></li>
    <li class="breadcrumb-item active">View Feature</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Homepage Feature Details</h5>
                        <p class="text-muted mb-0">View feature information</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ contextRoute('homepage-features.edit', $homepageFeature) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Feature
                        </a>
                        <a href="{{ contextRoute('homepage-features.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label class="form-label text-muted small">FEATURE TITLE</label>
                            <h4 class="fw-bold">{{ $homepageFeature->title }}</h4>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small">DESCRIPTION</label>
                            <p class="lead">{{ $homepageFeature->description }}</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">ICON CLASS</label>
                                <div class="d-flex align-items-center">
                                    <i class="{{ $homepageFeature->icon }} me-2" style="color: {{ $homepageFeature->color }}; font-size: 20px;"></i>
                                    <code class="bg-light px-2 py-1 rounded">{{ $homepageFeature->icon }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">ICON COLOR</label>
                                <div class="d-flex align-items-center">
                                    <div class="color-preview me-2" 
                                         style="width: 20px; height: 20px; background-color: {{ $homepageFeature->color }}; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                                    <code class="bg-light px-2 py-1 rounded">{{ $homepageFeature->color }}</code>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">SORT ORDER</label>
                                <p class="fw-semibold">{{ $homepageFeature->sort_order }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">STATUS</label>
                                <div>
                                    @if($homepageFeature->is_active)
                                        <span class="badge bg-success-soft text-success px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger px-3 py-2">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">CREATED DATE</label>
                                <p class="fw-semibold">{{ formatDateTime($homepageFeature->created_at) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">LAST UPDATED</label>
                                <p class="fw-semibold">{{ formatDateTime($homepageFeature->updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light border-0">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-eye me-2"></i>Feature Preview
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="feature-preview text-center p-3 bg-white rounded-3 shadow-sm">
                                    <div class="feature-icon mb-3">
                                        <i class="{{ $homepageFeature->icon }} fs-1" 
                                           style="color: {{ $homepageFeature->color }};"></i>
                                    </div>
                                    <h6 class="fw-bold mb-2">{{ $homepageFeature->title }}</h6>
                                    <p class="text-muted small mb-0">{{ Str::limit($homepageFeature->description, 80) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 mt-3">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-cogs me-2"></i>Quick Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ contextRoute('homepage-features.edit', $homepageFeature) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit me-2"></i>Edit Feature
                                    </a>
                                    
                                    <form action="{{ contextRoute('homepage-features.toggle-status', $homepageFeature) }}" 
                                          method="POST" class="status-toggle-form">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $homepageFeature->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                            <i class="fas fa-{{ $homepageFeature->is_active ? 'eye-slash' : 'eye' }} me-2"></i>
                                            {{ $homepageFeature->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form action="{{ contextRoute('homepage-features.destroy', $homepageFeature) }}" 
                                          method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="fas fa-trash me-2"></i>Delete Feature
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Status toggle form submission
    $('.status-toggle-form').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message and reload
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function() {
                button.prop('disabled', false).html(originalText);
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
    $('.delete-form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });
});
</script>
@endpush
@endsection
