@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Edit Department')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active">Edit Department</li>
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
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit Department</h1>
        <p class="page-subtitle text-muted">Update department information and settings</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editDepartmentForm" method="POST" action="{{ contextRoute('departments.update', $department->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Department Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-building me-2"></i>Department Information</h4>
                        <small class="opacity-75">Basic department details and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Department Name *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $department->name) }}" 
                                           placeholder="Enter department name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status *
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', $department->is_active ? 'active' : 'inactive') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $department->is_active ? 'active' : 'inactive') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Department Phone
                                    </label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $department->phone) }}" 
                                           placeholder="e.g., +000 123 456 789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="head_of_department" class="form-label">
                                        <i class="fas fa-user-tie me-1"></i>Head of Department
                                    </label>
                                    <input type="text" class="form-control @error('head_of_department') is-invalid @enderror" 
                                           id="head_of_department" name="head_of_department" value="{{ old('head_of_department', $department->head_of_department) }}"
                                           placeholder="Enter head of department">
                                    @error('head_of_department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Department Email
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $department->email) }}" 
                                           placeholder="department@hospital.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image me-1"></i>Department Image
                                    </label>
                                    @if($department->image)
                                        <div class="mb-2">
                                            <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}" 
                                                 alt="Current department image" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 200px; max-height: 150px;">
                                            <div class="form-help mt-1">Current image</div>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">Upload a new image to replace the current one (JPG, PNG, GIF - Max: 2MB)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Location
                        </h4>
                        <small class="opacity-75">Department location details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Location
                            </label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                   id="location" name="location" value="{{ old('location', $department->location) }}"
                                   placeholder="Enter department location (e.g., Building A, Floor 3, Room 301)">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Description</h4>
                        <small class="opacity-75">Additional department details and notes</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-audio-description me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="Enter department description">{{ old('description', $department->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="services" class="form-label">
                                <i class="fas fa-concierge-bell me-1"></i>Services Offered
                            </label>
                            <textarea class="form-control @error('services') is-invalid @enderror" 
                                      id="services" name="services" rows="3" 
                                      placeholder="List the services offered by this department (one per line)">{{ old('services', is_array($department->services) ? implode("\n", $department->services) : $department->services) }}</textarea>
                            @error('services')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-help">Enter each service on a new line</div>
                        </div>

                        <div class="form-group">
                            <label for="operating_hours" class="form-label">
                                <i class="fas fa-clock me-1"></i>Operating Hours
                            </label>
                            <input type="text" class="form-control @error('operating_hours') is-invalid @enderror" 
                                   id="operating_hours" name="operating_hours" value="{{ old('operating_hours', $department->operating_hours) }}" 
                                   placeholder="e.g., 24/7 or 8:00 AM - 6:00 PM">
                            @error('operating_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Department
                        </button>
                        <a href="{{ contextRoute('departments.index') }}" class="btn btn-secondary btn-lg">
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
                    <span class="status-badge {{ $department->status }}">{{ ucfirst($department->status) }}</span>
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ formatDate($department->created_at) }}
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong> {{ formatDate($department->updated_at) }}
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Update Guidelines</h6>
                <ul>
                    <li>Ensure department code remains unique</li>
                    <li>Update contact information if department head changes</li>
                    <li>Verify location details for accuracy</li>
                    <li>Keep service listings current and relevant</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Status Guidelines</h6>
                <ul>
                    <li><strong>Active:</strong> Department is operational and accepting patients</li>
                    <li><strong>Inactive:</strong> Department is temporarily closed or under maintenance</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('departments.show', $department->id) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View Department
                    </a>
                    <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>All Departments
                    </a>
                    <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-secondary btn-sm">
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
    // Form validation
    $('#editDepartmentForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $('.form-control[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate phone number format
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if ($('#phone').val() && !phoneRegex.test($('#phone').val())) {
            $('#phone').addClass('is-invalid');
            isValid = false;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if ($('#email').val() && !emailRegex.test($('#email').val())) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // Remove validation errors on input
    $('.form-control').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Character counter for textareas
    $('textarea').each(function() {
        const maxLength = 1000;
        const currentLength = $(this).val().length;
        $(this).after('<small class="text-muted char-counter">' + currentLength + '/' + maxLength + ' characters</small>');
        
        $(this).on('input', function() {
            const currentLength = $(this).val().length;
            $(this).next('.char-counter').text(currentLength + '/' + maxLength + ' characters');
            
            if (currentLength > maxLength) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    });
});
</script>
@endpush
