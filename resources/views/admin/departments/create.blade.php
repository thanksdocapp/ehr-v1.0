@extends('admin.layouts.app')

@section('title', 'Create New Clinic')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('departments.index') }}">Clinics</a></li>
    <li class="breadcrumb-item active">Create Clinic</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
    .service-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .service-item .form-control {
        flex: 1;
    }

    .image-preview-container {
        width: 200px;
        height: 150px;
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .image-preview-container:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .image-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-preview-placeholder {
        text-align: center;
        color: #a0aec0;
    }

    .image-preview-placeholder i {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .quick-action-card {
        background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .quick-action-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .quick-action-card h6 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quick-action-card ul {
        margin: 0;
        padding-left: 1.25rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    .quick-action-card ul li {
        margin-bottom: 0.4rem;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="modern-page-title">
                        <i class="fas fa-hospital-alt"></i>
                        Create New Clinic
                    </h1>
                    <p class="modern-page-subtitle">Add a new clinic/department to the system</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ contextRoute('departments.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="createDepartmentForm" method="POST" action="{{ contextRoute('departments.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Main Form Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-building"></i>
                            Basic Information
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-tag me-1"></i>Clinic Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control modern-form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="e.g., Cardiology Department"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select modern-form-select @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-user-tie me-1"></i>Head of Department
                                    </label>
                                    <input type="text"
                                           class="form-control modern-form-control @error('head_of_department') is-invalid @enderror"
                                           id="head_of_department"
                                           name="head_of_department"
                                           value="{{ old('head_of_department') }}"
                                           placeholder="e.g., Dr. John Smith">
                                    @error('head_of_department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Location
                                    </label>
                                    <input type="text"
                                           class="form-control modern-form-control @error('location') is-invalid @enderror"
                                           id="location"
                                           name="location"
                                           value="{{ old('location') }}"
                                           placeholder="e.g., Building A, Floor 3">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">
                                <i class="fas fa-align-left me-1"></i>Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control modern-form-control modern-form-textarea @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="Provide a detailed description of the clinic's purpose and specializations..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-help-text">Describe the clinic's main functions and areas of expertise</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-address-card"></i>
                            Contact Information
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-phone me-1"></i>Phone Number
                                    </label>
                                    <input type="text"
                                           class="form-control modern-form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="e.g., +44 20 7123 4567">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address
                                    </label>
                                    <input type="email"
                                           class="form-control modern-form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="e.g., cardiology@hospital.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">
                                <i class="fas fa-clock me-1"></i>Operating Hours
                            </label>
                            <input type="text"
                                   class="form-control modern-form-control @error('operating_hours') is-invalid @enderror"
                                   id="operating_hours"
                                   name="operating_hours"
                                   value="{{ old('operating_hours') }}"
                                   placeholder="e.g., Mon-Fri 8:00 AM - 6:00 PM, Sat 9:00 AM - 1:00 PM">
                            @error('operating_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Services Offered -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-concierge-bell"></i>
                            Services Offered
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div id="services-container">
                            @php
                                $oldServices = old('services', ['']);
                                if (is_string($oldServices)) {
                                    $oldServices = [$oldServices];
                                }
                            @endphp
                            @foreach($oldServices as $index => $service)
                            <div class="service-item">
                                <input type="text"
                                       class="form-control modern-form-control"
                                       name="services[]"
                                       value="{{ $service }}"
                                       placeholder="e.g., ECG Testing, Cardiac Catheterization">
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm remove-service"
                                        {{ $index === 0 ? 'style=display:none;' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-service">
                            <i class="fas fa-plus me-1"></i>Add Service
                        </button>
                        @error('services')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                        @error('services.*')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Department Image -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-image"></i>
                            Clinic Image
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="image-preview-container" id="imagePreviewContainer">
                                    <div class="image-preview-placeholder">
                                        <i class="fas fa-camera d-block"></i>
                                        <span class="small">Preview</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="modern-form-group mb-0">
                                    <label class="modern-form-label">Upload Image</label>
                                    <input type="file"
                                           class="form-control modern-form-control @error('image') is-invalid @enderror"
                                           id="image"
                                           name="image"
                                           accept=".jpg,.jpeg,.png,.gif,.webp">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Accepted formats: JPG, PNG, GIF, WebP (Max: 2MB)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <span class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Fields marked with <span class="text-danger">*</span> are required
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ contextRoute('departments.index') }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-modern btn-modern-primary">
                                    <i class="fas fa-save me-2"></i>Create Clinic
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Guidelines -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-info-circle text-primary"></i>
                        Guidelines
                    </h6>
                    <ul>
                        <li>All fields marked with * are required</li>
                        <li>Clinic name should be unique and descriptive</li>
                        <li>Provide accurate contact information</li>
                        <li>List all services the clinic offers</li>
                    </ul>
                </div>

                <!-- Best Practices -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-lightbulb text-warning"></i>
                        Best Practices
                    </h6>
                    <ul>
                        <li>Write clear, concise descriptions</li>
                        <li>Keep service listings up to date</li>
                        <li>Specify accurate operating hours</li>
                        <li>Upload a representative image</li>
                    </ul>
                </div>

                <!-- Quick Actions -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-bolt text-info"></i>
                        Quick Actions
                    </h6>
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>View All Clinics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreviewContainer').html('<img src="' + e.target.result + '" alt="Preview">');
            };
            reader.readAsDataURL(file);
        } else {
            $('#imagePreviewContainer').html(`
                <div class="image-preview-placeholder">
                    <i class="fas fa-camera d-block"></i>
                    <span class="small">Preview</span>
                </div>
            `);
        }
    });

    // Form validation
    $('#createDepartmentForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $(this).find('.form-control[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate email format
        const email = $('#email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });

    // Remove validation errors on input
    $('.form-control').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Dynamic services management
    $('#add-service').on('click', function() {
        const serviceItem = $(`
            <div class="service-item">
                <input type="text" class="form-control modern-form-control"
                       name="services[]"
                       placeholder="e.g., ECG Testing, Cardiac Catheterization">
                <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `);
        $('#services-container').append(serviceItem);
        updateRemoveButtons();
    });

    // Remove service functionality
    $(document).on('click', '.remove-service', function() {
        $(this).closest('.service-item').remove();
        updateRemoveButtons();
    });

    // Update remove buttons visibility
    function updateRemoveButtons() {
        const serviceItems = $('.service-item');
        if (serviceItems.length === 1) {
            serviceItems.find('.remove-service').hide();
        } else {
            serviceItems.find('.remove-service').show();
        }
    }

    // Initialize remove buttons on page load
    updateRemoveButtons();
});
</script>
@endpush
