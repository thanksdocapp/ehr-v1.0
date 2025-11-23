@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'About Us Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">About Us</li>
@endsection

@section('content')
<div class="page-title">
    <h1>About Us Management</h1>
    <p class="page-subtitle">Manage your hospital's About Us page content</p>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Current About Us Content Display -->
<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Current About Us Content</h3>
                <a href="{{ contextRoute('about.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Content
                </a>
            </div>
            <div class="card-body">
                @if($settings)
                    <div class="content-preview">
                        <div class="content-section mb-4">
                            <h5 class="section-title">Hero Section</h5>
                            <div class="content-item">
                                <strong>Title:</strong>
                                <p class="content-text">{{ $settings['about_hero_title'] ?? 'Not set' }}</p>
                            </div>
                            <div class="content-item">
                                <strong>Subtitle:</strong>
                                <p class="content-text">{{ $settings['about_hero_subtitle'] ?? 'Not set' }}</p>
                            </div>
                        </div>
                        
                        <div class="content-section mb-4">
                            <h5 class="section-title">Main Content</h5>
                            <div class="content-item">
                                <strong>Title:</strong>
                                <p class="content-text">{{ $settings['about_main_title'] ?? 'Not set' }}</p>
                            </div>
                            <div class="content-item">
                                <strong>Description:</strong>
                                <p class="content-text">{{ $settings['about_main_description'] ?? 'Not set' }}</p>
                            </div>
                            <div class="content-item">
                                <strong>Full Content:</strong>
                                <div class="content-text formatted-content">{{ $settings['about_main_content'] ?? 'Not set' }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No About Us content has been set yet</h5>
                        <p class="text-muted">Click "Edit Content" to add your hospital's About Us information</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Current Image</h3>
            </div>
            <div class="card-body text-center">
                @if($settings && isset($settings['about_image']))
                    <div class="image-preview">
                        @if(str_starts_with($settings['about_image'], 'assets/'))
                            <img src="{{ asset($settings['about_image']) }}" 
                                 alt="{{ $settings['about_image_alt'] ?? 'About Us Image' }}" 
                                 class="img-fluid rounded shadow current-image">
                        @else
                            <img src="{{ Storage::disk('public')->url($settings['about_image']) }}" 
                                 alt="{{ $settings['about_image_alt'] ?? 'About Us Image' }}" 
                                 class="img-fluid rounded shadow current-image">
                        @endif
                        <div class="image-info mt-2">
                            <small class="text-muted">Alt Text: {{ $settings['about_image_alt'] ?? 'Not set' }}</small>
                        </div>
                    </div>
                @else
                    <div class="no-image-placeholder">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No image uploaded</p>
                    </div>
                @endif
                
                <div class="mt-3">
                    <a href="{{ contextRoute('about.resetImage') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-undo me-2"></i>Reset to Default
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Content Status</h3>
            </div>
            <div class="card-body">
                <div class="status-items">
                    <div class="status-item">
                        <span class="status-label">Hero Title:</span>
                        <span class="status-value {{ $settings && isset($settings['about_hero_title']) ? 'text-success' : 'text-danger' }}">
                            {{ $settings && isset($settings['about_hero_title']) ? 'Set' : 'Not Set' }}
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Hero Subtitle:</span>
                        <span class="status-value {{ $settings && isset($settings['about_hero_subtitle']) ? 'text-success' : 'text-danger' }}">
                            {{ $settings && isset($settings['about_hero_subtitle']) ? 'Set' : 'Not Set' }}
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Main Title:</span>
                        <span class="status-value {{ $settings && isset($settings['about_main_title']) ? 'text-success' : 'text-danger' }}">
                            {{ $settings && isset($settings['about_main_title']) ? 'Set' : 'Not Set' }}
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Main Content:</span>
                        <span class="status-value {{ $settings && isset($settings['about_main_content']) ? 'text-success' : 'text-danger' }}">
                            {{ $settings && isset($settings['about_main_content']) ? 'Set' : 'Not Set' }}
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Image:</span>
                        <span class="status-value {{ $settings && isset($settings['about_image']) ? 'text-success' : 'text-warning' }}">
                            {{ $settings && isset($settings['about_image']) ? 'Uploaded' : 'Default' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('styles')
<style>
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: #374151;
    }
    
    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-outline-secondary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }
</style>
@endpush
