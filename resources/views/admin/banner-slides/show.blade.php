@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'View Banner Slide')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">View Banner Slide</h1>
        <div>
            <a href="{{ contextRoute('banner-slides.edit', $slide) }}" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ contextRoute('banner-slides.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Banner Slide Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Title:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $slide->title }}
                        </div>
                    </div>

                    @if($slide->subtitle)
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Subtitle:</strong>
                            </div>
                            <div class="col-md-9">
                                {{ $slide->subtitle }}
                            </div>
                        </div>
                    @endif

                    @if($slide->description)
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Description:</strong>
                            </div>
                            <div class="col-md-9">
                                {{ $slide->description }}
                            </div>
                        </div>
                    @endif

                    @if($slide->button_text)
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Button Text:</strong>
                            </div>
                            <div class="col-md-9">
                                {{ $slide->button_text }}
                            </div>
                        </div>
                    @endif

                    @if($slide->button_url)
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Button URL:</strong>
                            </div>
                            <div class="col-md-9">
                                <a href="{{ $slide->button_url }}" target="_blank" class="text-primary">
                                    {{ $slide->button_url }}
                                    <i class="fas fa-external-link-alt fa-sm"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Sort Order:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $slide->sort_order }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Background Color:</strong>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex align-items-center">
                                <div style="width: 30px; height: 20px; background-color: {{ $slide->background_color }}; border: 1px solid #ddd; margin-right: 8px;"></div>
                                <span>{{ $slide->background_color }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Status:</strong>
                        </div>
                        <div class="col-md-9">
                            @if($slide->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Created:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ formatDateTime($slide->created_at) }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Last Updated:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ formatDateTime($slide->updated_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Banner Image</h6>
                </div>
                <div class="card-body text-center">
                    @if($slide->image)
                        <img src="{{ Storage::disk('public')->url($slide->image) }}" alt="{{ $slide->title }}" 
                             class="img-fluid rounded mb-3" style="max-height: 300px;">
                        <div class="text-muted small">
                            <strong>Filename:</strong> {{ basename($slide->image) }}
                        </div>
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                             style="height: 200px; border: 2px dashed #ddd;">
                            <div class="text-center">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <p class="text-muted">No image uploaded</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('banner-slides.edit', $slide) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Banner Slide
                        </a>
                        
                        <button type="button" class="btn btn-{{ $slide->is_active ? 'secondary' : 'success' }}" 
                                id="toggle-status" data-id="{{ $slide->id }}">
                            <i class="fas fa-{{ $slide->is_active ? 'pause' : 'play' }}"></i> 
                            {{ $slide->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        
                        <button type="button" class="btn btn-danger" onclick="deleteSlide({{ $slide->id }})">
                            <i class="fas fa-trash"></i> Delete Banner Slide
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Preview</h6>
        </div>
        <div class="card-body">
            <div class="banner-preview" 
                 style="background-color: {{ $slide->background_color }}; 
                        padding: 60px 20px; 
                        border-radius: 10px; 
                        text-align: center; 
                        color: {{ $slide->background_color === '#ffffff' ? '#000' : '#fff' }};">
                
                @if($slide->image)
                    <div class="mb-4">
                        <img src="{{ Storage::disk('public')->url($slide->image) }}" alt="{{ $slide->title }}" 
                             class="img-fluid rounded" style="max-height: 250px;">
                    </div>
                @endif
                
                <h1 class="display-4 mb-3">{{ $slide->title }}</h1>
                
                @if($slide->subtitle)
                    <h3 class="mb-3">{{ $slide->subtitle }}</h3>
                @endif
                
                @if($slide->description)
                    <p class="lead mb-4">{{ $slide->description }}</p>
                @endif
                
                @if($slide->button_text && $slide->button_url)
                    <a href="{{ $slide->button_url }}" class="btn btn-primary btn-lg">
                        {{ $slide->button_text }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('#toggle-status').click(function() {
        const slideId = $(this).data('id');
        const button = $(this);
        
        $.ajax({
            url: `/admin/banner-slides/${slideId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                toastr.error('Error updating status');
            }
        });
    });
    
    // Delete confirmation - using comprehensive confirmation dialog
    window.deleteSlide = function(slideId) {
        console.log('Delete slide called with ID:', slideId);
        
        // Prevent any default behavior if event exists
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Handle both sync and async confirm dialogs
        function handleConfirmation(confirmResult) {
            console.log('User confirmation result:', confirmResult);
            
            if (confirmResult === true) {
                console.log('User confirmed deletion, proceeding...');
                
                // Add a small delay to ensure the dialog is properly closed
                setTimeout(() => {
                    console.log('Creating form for deletion...');
                    
                    // Create a form to submit the DELETE request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/banner-slides/${slideId}`;
                    form.style.display = 'none';
                    
                    // Add CSRF token - try multiple methods
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    
                    // Try to get CSRF token from meta tag or Laravel's global
                    let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                        csrfTokenValue = Laravel.csrfToken;
                    }
                    if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                        csrfTokenValue = window.Laravel.csrfToken;
                    }
                    
                    csrfToken.value = csrfTokenValue;
                    form.appendChild(csrfToken);
                    
                    console.log('CSRF token:', csrfTokenValue);
                    
                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Form children:', form.children);
                    
                    // Add form to document and submit
                    document.body.appendChild(form);
                    console.log('Form added to document, submitting...');
                    form.submit();
                }, 100);
            } else {
                console.log('User cancelled deletion');
            }
        }
        
        // Use a more explicit confirmation dialog
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this banner slide?\n\nThis action cannot be undone and will remove all slide data including:\n- Slide content and images\n- Button links and settings\n- Display configurations\n- Background colors\n\nClick OK to confirm deletion or Cancel to abort.');
        
        // Handle both Promise and boolean returns
        if (confirmDelete && typeof confirmDelete.then === 'function') {
            // If it's a Promise, wait for it to resolve
            confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
        } else {
            // If it's a boolean, handle it directly
            handleConfirmation(confirmDelete);
        }
        
        return false;
    };
});
</script>
@endpush
