@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Banner Slides Management')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item active">Banner Slides</li>
@endsection

@push('styles')
<style>
.slides-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.slide-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.slide-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.slide-preview {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.slide-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.slide-card:hover .slide-preview img {
    transform: scale(1.05);
}

.slide-preview-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.slide-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.5), rgba(0,0,0,0.2));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.slide-card:hover .slide-overlay {
    opacity: 1;
}

.slide-actions {
    display: flex;
    gap: 0.5rem;
}

.slide-content {
    padding: 1.5rem;
}

.slide-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.slide-subtitle {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.slide-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.slide-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.status-badge.inactive {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
}

.sort-order {
    background: #f8f9fa;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.color-indicator {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.empty-state-icon {
    font-size: 4rem;
    color: #e9ecef;
    margin-bottom: 1.5rem;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    border: 1px solid #e3e6f0;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

.btn-action {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #28a745, #20c997);
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-images me-2 text-primary"></i>
                Banner Slides Management
            </h1>
            <p class="text-muted mt-1 mb-0">Manage your website's banner slides and carousel content</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ contextRoute('banner-slides.create') }}" class="btn btn-primary btn-action">
                <i class="fas fa-plus me-2"></i>Add New Slide
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-number">{{ $slides->count() }}</div>
            <div class="stat-label">Total Slides</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $slides->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active Slides</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $slides->where('is_active', false)->count() }}</div>
            <div class="stat-label">Inactive Slides</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $slides->whereNotNull('image')->count() }}</div>
            <div class="stat-label">With Images</div>
        </div>
    </div>

    @if($slides->count() > 0)
        <div class="slides-grid">
            @foreach($slides as $slide)
                <div class="slide-card">
                    <div class="slide-preview">
                        @if($slide->image)
                            <img src="{{ Storage::disk('public')->url($slide->image) }}" alt="{{ $slide->title }}">
                        @else
                            <div class="slide-preview-placeholder" style="background: {{ $slide->background_color }}">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                        <div class="slide-overlay">
                            <div class="slide-actions">
                                <a href="{{ contextRoute('banner-slides.show', $slide) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ contextRoute('banner-slides.edit', $slide) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSlide({{ $slide->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="slide-content">
                        <h3 class="slide-title">{{ $slide->title }}</h3>
                        @if($slide->subtitle)
                            <p class="slide-subtitle">{{ $slide->subtitle }}</p>
                        @endif
                        <div class="slide-meta">
                            <div class="slide-status">
                                <label class="switch">
                                    <input type="checkbox" class="toggle-status" data-id="{{ $slide->id }}" {{ $slide->is_active ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span class="status-badge {{ $slide->is_active ? 'active' : 'inactive' }}">
                                    {{ $slide->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="slide-actions">
                                    <a href="{{ contextRoute('banner-slides.show', $slide) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ contextRoute('banner-slides.edit', $slide) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSlide({{ $slide->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="color-indicator" style="background-color: {{ $slide->background_color }}"></div>
                                <span class="sort-order">Order: {{ $slide->sort_order }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-images"></i>
            </div>
            <h3 class="h4 mb-3">No Banner Slides Found</h3>
            <p class="text-muted mb-4">Create your first banner slide to showcase your content and engage visitors.</p>
            <a href="{{ contextRoute('banner-slides.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Create First Slide
            </a>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status').change(function() {
        const slideId = $(this).data('id');
        const isActive = $(this).is(':checked');
        
        $.ajax({
            url: `/admin/banner-slides/${slideId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Error updating status');
                // Revert checkbox
                $('.toggle-status[data-id="' + slideId + '"]').prop('checked', !isActive);
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
