@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Edit Clinic')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('departments.index') }}">Clinics</a></li>
    <li class="breadcrumb-item active">Edit {{ $department->name }}</li>
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

    .status-info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .status-info-item:last-child {
        border-bottom: none;
    }

    .status-info-label {
        color: #64748b;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .status-info-value {
        color: #2d3748;
        font-weight: 600;
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
                        <i class="fas fa-edit"></i>
                        Edit Clinic
                    </h1>
                    <p class="modern-page-subtitle">Update information for {{ $department->name }}</p>
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-secondary' }}">
                            <i class="fas {{ $department->is_active ? 'fa-check-circle' : 'fa-pause-circle' }} me-1"></i>
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($department->is_emergency)
                            <span class="badge bg-danger">
                                <i class="fas fa-bolt me-1"></i>Emergency
                            </span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ contextRoute('departments.show', $department->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View
                    </a>
                    <a href="{{ contextRoute('departments.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="editDepartmentForm" method="POST" action="{{ contextRoute('departments.update', $department->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                                           value="{{ old('name', $department->name) }}"
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
                                        <option value="active" {{ old('status', $department->is_active ? 'active' : 'inactive') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $department->is_active ? 'active' : 'inactive') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                           value="{{ old('head_of_department', $department->head_of_department) }}"
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
                                           value="{{ old('location', $department->location) }}"
                                           placeholder="e.g., Building A, Floor 3">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control modern-form-control modern-form-textarea @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="Provide a detailed description of the clinic's purpose and specializations...">{{ old('description', $department->description) }}</textarea>
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
                                           value="{{ old('phone', $department->phone) }}"
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
                                           value="{{ old('email', $department->email) }}"
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
                                   value="{{ old('operating_hours', $department->operating_hours) }}"
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
                        <div class="modern-form-group">
                            <label class="modern-form-label">Services</label>
                            <textarea class="form-control modern-form-control @error('services') is-invalid @enderror"
                                      id="services"
                                      name="services"
                                      rows="4"
                                      placeholder="List the services offered by this clinic (one per line)">{{ old('services', is_array($department->services) ? implode("\n", $department->services) : $department->services) }}</textarea>
                            @error('services')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-help-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Enter each service on a new line
                            </div>
                        </div>
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
                                    @if($department->image)
                                        <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}"
                                             alt="{{ $department->name }}">
                                    @else
                                        <div class="image-preview-placeholder">
                                            <i class="fas fa-camera d-block"></i>
                                            <span class="small">No Image</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="modern-form-group mb-0">
                                    <label class="modern-form-label">Upload New Image</label>
                                    <input type="file"
                                           class="form-control modern-form-control @error('image') is-invalid @enderror"
                                           id="image"
                                           name="image"
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Upload a new image to replace the current one (JPG, PNG, GIF - Max: 2MB)
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
                                    <i class="fas fa-save me-2"></i>Update Clinic
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-info-circle text-primary"></i>
                        Current Status
                    </h6>
                    <div class="status-info-item">
                        <span class="status-info-label">Status</span>
                        <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="status-info-item">
                        <span class="status-info-label">Created</span>
                        <span class="status-info-value">{{ formatDate($department->created_at) }}</span>
                    </div>
                    <div class="status-info-item">
                        <span class="status-info-label">Last Updated</span>
                        <span class="status-info-value">{{ formatDate($department->updated_at) }}</span>
                    </div>
                    @if($department->doctors_count ?? 0 > 0)
                    <div class="status-info-item">
                        <span class="status-info-label">Doctors</span>
                        <span class="status-info-value">{{ $department->doctors_count ?? 0 }}</span>
                    </div>
                    @endif
                </div>

                <!-- Update Guidelines -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-lightbulb text-warning"></i>
                        Update Guidelines
                    </h6>
                    <ul>
                        <li>Ensure clinic name remains unique</li>
                        <li>Update contact info if department head changes</li>
                        <li>Verify location details for accuracy</li>
                        <li>Keep service listings current</li>
                    </ul>
                </div>

                <!-- Status Guidelines -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-shield-alt text-success"></i>
                        Status Guidelines
                    </h6>
                    <ul>
                        <li><strong>Active:</strong> Clinic is operational and accepting patients</li>
                        <li><strong>Inactive:</strong> Clinic is temporarily closed or under maintenance</li>
                    </ul>
                </div>

                <!-- Quick Actions -->
                <div class="quick-action-card fade-in-up">
                    <h6>
                        <i class="fas fa-bolt text-info"></i>
                        Quick Actions
                    </h6>
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('departments.show', $department->id) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-1"></i>View Clinic
                        </a>
                        <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>All Clinics
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
        }
    });

    // Form validation
    $('#editDepartmentForm').on('submit', function(e) {
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
});
</script>
@endpush
