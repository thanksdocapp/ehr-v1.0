@extends('admin.layouts.app')

@section('title', 'Edit Service')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('services.index') }}">Services</a></li>
    <li class="breadcrumb-item active">Edit Service</li>
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

.current-image {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    background-color: #f8f9fc;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.current-image img {
    max-width: 100%;
    max-height: 130px;
    border-radius: 8px;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.active {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit Service</h1>
        <p class="page-subtitle text-muted">Update service information and settings</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editServiceForm" method="POST" action="{{ contextRoute('services.update', $service) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Service Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Service Information</h4>
                        <small class="opacity-75">Basic service details and description</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Service Name *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $service->name) }}" 
                                           placeholder="Enter service name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="department_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>Clinic *
                                    </label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id" required>
                                        <option value="">Select Clinic</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $service->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="form-label">
                                        <i class="fas fa-dollar-sign me-1"></i>Price ($)
                                    </label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $service->price) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="duration" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Duration (minutes)
                                    </label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                           id="duration" name="duration" value="{{ old('duration', $service->duration) }}" 
                                           min="1" placeholder="30">
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Description *
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Enter detailed service description" required>{{ old('description', $service->description) }}</textarea>
                            <div class="form-help">Provide comprehensive information about this service</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Service Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Service Settings</h4>
                        <small class="opacity-75">Service status and configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image me-1"></i>Service Image
                                    </label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    <div class="form-help">Upload new image to replace current one (JPEG, PNG, JPG, GIF - Max: 2MB)</div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active Service
                                        </label>
                                    </div>
                                    <div class="form-help">Enable this service for patient bookings</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Current Image</label>
                                    <div class="current-image">
                                        @if($service->image)
                                            <img src="{{ asset('storage/' . $service->image) }}" 
                                                 alt="{{ $service->name }}" class="img-fluid rounded">
                                        @else
                                            <i class="fas fa-image text-muted fa-3x"></i>
                                            <p class="text-muted mt-2">No image uploaded</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">New Image Preview</label>
                                    <div id="image-preview" class="image-preview">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                        <p class="text-muted mt-2">No new image selected</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Service
                        </button>
                        <a href="{{ contextRoute('services.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Current Status</h6>
                <div class="mb-3">
                    <strong>Status:</strong> 
                    <span class="status-badge {{ $service->is_active ? 'active' : 'inactive' }}">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Department:</strong> {{ $service->department->name }}
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ formatDate($service->created_at) }}
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong> {{ formatDate($service->updated_at) }}
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Update Guidelines</h6>
                <ul>
                    <li>Ensure service name remains descriptive and unique</li>
                    <li>Update department if service has been moved</li>
                    <li>Adjust pricing based on current market rates</li>
                    <li>Keep service descriptions current and accurate</li>
                    <li>Upload new images only when necessary</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Status Guidelines</h6>
                <ul>
                    <li><strong>Active:</strong> Service is available for patient bookings</li>
                    <li><strong>Inactive:</strong> Service is temporarily unavailable</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('services.show', $service) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View Service
                    </a>
                    <a href="{{ contextRoute('services.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>All Services
                    </a>
                    <a href="{{ contextRoute('services.index') }}" class="btn btn-outline-secondary btn-sm">
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
    // Image preview
    $('#image').change(function(e) {
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
                <p class="text-muted mt-2">No new image selected</p>
            `);
        }
    });

    // Form validation
    $('#editServiceForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $('.form-control[required], .form-select[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate price
        const price = parseFloat($('#price').val());
        if (price && price < 0) {
            $('#price').addClass('is-invalid');
            isValid = false;
        }

        // Validate duration
        const duration = parseInt($('#duration').val());
        if (duration && duration < 1) {
            $('#duration').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // Remove validation errors on input
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Character counter for description
    $('#description').on('input', function() {
        const maxLength = 2000;
        const currentLength = $(this).val().length;
        
        if (!$(this).next('.char-counter').length) {
            $(this).after('<small class="text-muted char-counter"></small>');
        }
        
        $(this).next('.char-counter').text(currentLength + '/' + maxLength + ' characters');
        
        if (currentLength > maxLength) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Initialize character counter
    $('#description').trigger('input');
});
</script>
@endpush
