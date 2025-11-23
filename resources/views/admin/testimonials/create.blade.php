@extends('admin.layouts.app')

@section('title', 'Create Testimonial')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('testimonials.index') }}">Testimonials</a></li>
    <li class="breadcrumb-item active">Create Testimonial</li>
@endsection

@push('styles')
<style>
    .form-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        border: 1px solid #e3e6f0;
    }
    .form-section-header {
        background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
    }
    .form-section-body {
        padding: 2rem;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .form-control, .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #1cc88a;
        box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
    }
    .btn {
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
    }
    .form-help {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.5rem;
        font-style: italic;
    }
    .image-preview {
        border: 2px dashed #e3e6f0;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    .image-preview:hover {
        border-color: #1cc88a;
        background-color: #f8f9fc;
    }
    .image-preview img {
        max-width: 100%;
        max-height: 180px;
        border-radius: 8px;
    }
    .info-card {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .info-card h6 {
        color: #5a5c69;
        margin-bottom: 1rem;
    }
    .info-card ul {
        margin: 0;
        padding-left: 1.5rem;
    }
    .info-card li {
        margin-bottom: 0.5rem;
        color: #858796;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-plus me-2 text-primary"></i>Create Testimonial</h1>
        <p class="page-subtitle text-muted">Add a customer testimonial to your website</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createTestimonialForm" method="POST" action="{{ contextRoute('testimonials.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Testimonial Content Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Testimonial Content</h4>
                        <small class="opacity-75">Details of the customer testimonial</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="customer_name" class="form-label">
                                <i class="fas fa-user me-1"></i>Customer Name *
                            </label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                   id="customer_name" name="customer_name" value="{{ old('customer_name') }}" 
                                   placeholder="Enter customer name" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_position" class="form-label">
                                <i class="fas fa-briefcase me-1"></i>Position
                            </label>
                            <input type="text" class="form-control @error('customer_position') is-invalid @enderror" 
                                   id="customer_position" name="customer_position" value="{{ old('customer_position') }}" 
                                   placeholder="Enter customer position">
                            <div class="form-help">Customer's job position or title</div>
                            @error('customer_position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_company" class="form-label">
                                <i class="fas fa-building me-1"></i>Company
                            </label>
                            <input type="text" class="form-control @error('customer_company') is-invalid @enderror" 
                                   id="customer_company" name="customer_company" value="{{ old('customer_company') }}" 
                                   placeholder="Enter company name">
                            <div class="form-help">Customer's company or organization</div>
                            @error('customer_company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="review_text" class="form-label">
                                <i class="fas fa-comment-dots me-1"></i>Review *
                            </label>
                            <textarea class="form-control @error('review_text') is-invalid @enderror" 
                                      id="review_text" name="review_text" rows="4" 
                                      placeholder="Enter customer review" required>{{ old('review_text') }}</textarea>
                            <div class="form-help">Customer's testimonial text</div>
                            @error('review_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Testimonial Additional Details -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-image me-2"></i>Additional Details</h4>
                        <small class="opacity-75">Photo and rating details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="customer_photo" class="form-label">
                                <i class="fas fa-image me-1"></i>Customer Photo
                            </label>
                            <input type="file" class="form-control @error('customer_photo') is-invalid @enderror" 
                                   id="customer_photo" name="customer_photo" accept="image/*">
                            <div class="form-help">Upload customer photo (JPG, PNG, GIF, WebP - Max: 2MB)</div>
                            @error('customer_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="rating" class="form-label">
                                <i class="fas fa-star me-1"></i>Rating
                            </label>
                            <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating">
                                <option value="">Select Rating</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} Star</option>
                                @endfor
                            </select>
                            <div class="form-help">Rate the experience from 1 to 5 stars</div>
                            @error('rating')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Testimonial
                        </button>
                        <a href="{{ contextRoute('testimonials.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Testimonial Guidelines</h6>
                <ul>
                    <li>Ensure customer name is accurate</li>
                    <li>Upload high-quality photo for best display</li>
                    <li>Provide insightful and concise reviews</li>
                    <li>Use 1-5 star rating to reflect experience</li>
                    <li>Ensure consistency with brand messaging</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Design Tips</h6>
                <ul>
                    <li>Maintain uniform style across all testimonials</li>
                    <li>Optimize images for faster loading</li>
                    <li>Testimonial text should be legible and engaging</li>
                    <li>Consider customer privacy when displaying photos</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('testimonials.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Testimonials
                    </a>
                    <a href="{{ contextRoute('testimonials.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#customer_photo').change(function(e) {
        const file = e.target.files[0];
        const preview = $('#image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html(`
                    <img src="${e.target.result}" alt="Preview" class="img-fluid rounded">
                `);
            }
            reader.readAsDataURL(file);
        } else {
            preview.html(`
                <i class="fas fa-image text-muted fa-3x"></i>
                <p class="text-muted mt-2">No image selected</p>
            `);
        }
    });
});
</script>
@endpush

