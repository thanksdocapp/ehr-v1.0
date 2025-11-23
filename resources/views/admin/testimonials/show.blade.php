@extends('admin.layouts.app')

@section('title', 'View Testimonial')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('testimonials.index') }}">Testimonials</a></li>
    <li class="breadcrumb-item active">View Testimonial</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-eye me-2 text-primary"></i>View Testimonial</h1>
        <p class="page-subtitle text-muted">Testimonial details and information</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Testimonial Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Testimonial Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($testimonial->customer_photo)
                                <img src="{{ asset('storage/' . $testimonial->customer_photo) }}" 
                                     alt="{{ $testimonial->customer_name }}" 
                                     class="rounded-circle mb-3" 
                                     width="120" height="120"
                                     style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mb-3 mx-auto" 
                                     style="width: 120px; height: 120px; font-size: 48px;">
                                    {{ strtoupper(substr($testimonial->customer_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h3 class="mb-1">{{ $testimonial->customer_name }}</h3>
                            @if($testimonial->customer_position)
                                <p class="text-muted mb-1">{{ $testimonial->customer_position }}</p>
                            @endif
                            @if($testimonial->customer_company)
                                <p class="text-primary mb-2"><i class="fas fa-building me-1"></i>{{ $testimonial->customer_company }}</p>
                            @endif
                            
                            @if($testimonial->rating)
                                <div class="mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial->rating)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-muted"></i>
                                        @endif
                                    @endfor
                                    <span class="ms-2 text-muted">({{ $testimonial->rating }}/5)</span>
                                </div>
                            @endif

                            <div class="mb-3">
                                <span class="badge {{ $testimonial->is_active ? 'bg-success' : 'bg-secondary' }} fs-6">
                                    <i class="fas {{ $testimonial->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                    {{ $testimonial->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Text Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-quote-left me-2"></i>Customer Review</h5>
                </div>
                <div class="card-body">
                    <blockquote class="blockquote">
                        <p class="mb-0">"{{ $testimonial->review_text }}"</p>
                        <footer class="blockquote-footer mt-2">
                            {{ $testimonial->customer_name }}
                            @if($testimonial->customer_position && $testimonial->customer_company)
                                <cite title="Source Title">{{ $testimonial->customer_position }} at {{ $testimonial->customer_company }}</cite>
                            @elseif($testimonial->customer_position)
                                <cite title="Source Title">{{ $testimonial->customer_position }}</cite>
                            @elseif($testimonial->customer_company)
                                <cite title="Source Title">{{ $testimonial->customer_company }}</cite>
                            @endif
                        </footer>
                    </blockquote>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4">
                <a href="{{ contextRoute('testimonials.edit', $testimonial) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Testimonial
                </a>
                <a href="{{ contextRoute('testimonials.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
                <form action="{{ contextRoute('testimonials.destroy', $testimonial) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this testimonial?')">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Testimonial Meta -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Testimonial Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small">Status</label>
                            <div>
                                <span class="badge {{ $testimonial->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $testimonial->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        
                        @if($testimonial->rating)
                        <div class="col-12">
                            <label class="form-label text-muted small">Rating</label>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $testimonial->rating)
                                        <i class="fas fa-star text-warning"></i>
                                    @else
                                        <i class="far fa-star text-muted"></i>
                                    @endif
                                @endfor
                                <span class="text-muted ms-1">({{ $testimonial->rating }}/5)</span>
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label text-muted small">Created Date</label>
                            <div>{{ formatDateTime($testimonial->created_at) }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small">Last Updated</label>
                            <div>{{ formatDateTime($testimonial->updated_at) }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small">Review Length</label>
                            <div>{{ strlen($testimonial->review_text) }} characters</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('testimonials.edit', $testimonial) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit Details
                        </a>
                        
                        <form action="{{ contextRoute('testimonials.toggle-status', $testimonial) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $testimonial->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                <i class="fas fa-toggle-{{ $testimonial->is_active ? 'off' : 'on' }} me-1"></i>
                                {{ $testimonial->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <a href="{{ contextRoute('testimonials.create') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-plus me-1"></i>Add New Testimonial
                        </a>
                        
                        <a href="{{ contextRoute('testimonials.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list me-1"></i>All Testimonials
                        </a>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Frontend Preview</h6>
                </div>
                <div class="card-body">
                    <div class="testimonial-preview p-3 border rounded">
                        <div class="d-flex mb-3">
                            @if($testimonial->customer_photo)
                                <img src="{{ asset('storage/' . $testimonial->customer_photo) }}" 
                                     alt="{{ $testimonial->customer_name }}" 
                                     class="rounded-circle me-3" 
                                     width="50" height="50"
                                     style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-3" 
                                     style="width: 50px; height: 50px; font-size: 18px;">
                                    {{ strtoupper(substr($testimonial->customer_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $testimonial->customer_name }}</h6>
                                @if($testimonial->customer_position)
                                    <small class="text-muted">{{ $testimonial->customer_position }}</small>
                                @endif
                                @if($testimonial->customer_company)
                                    <br><small class="text-primary">{{ $testimonial->customer_company }}</small>
                                @endif
                            </div>
                        </div>
                        
                        @if($testimonial->rating)
                            <div class="mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $testimonial->rating)
                                        <i class="fas fa-star text-warning" style="font-size: 14px;"></i>
                                    @else
                                        <i class="far fa-star text-muted" style="font-size: 14px;"></i>
                                    @endif
                                @endfor
                            </div>
                        @endif
                        
                        <p class="mb-0 small">"{{ Str::limit($testimonial->review_text, 120) }}"</p>
                    </div>
                    <small class="text-muted">This is how the testimonial will appear on your website.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
