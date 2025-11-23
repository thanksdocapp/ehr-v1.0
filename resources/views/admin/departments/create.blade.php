@extends('admin.layouts.app')

@section('title', 'Create Department')

@section('breadcrumb')
    	<li class="breadcrumb-item">	
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    	<li class="breadcrumb-item">
        <a href="{{ contextRoute('departments.index') }}">Departments</a>
    </li>
    
    	<li class="breadcrumb-item active">Create Department</li>
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1>
            <i class="fas fa-hospital me-2 text-primary"></i>
            Create New Department
        </h1>
        <p class="page-subtitle text-muted">Add a new department to the system with comprehensive details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createDepartmentForm" method="POST" action="{{ contextRoute('departments.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Department Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0">
                            <i class="fas fa-building me-2"></i>
                            Department Information
                        </h4>
                        <small class="opacity-75">Basic department details and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>
                                        Department Name *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Enter department name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>
                                        Status *
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>
                                        Department Phone
                                    </label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="e.g., +000 123 456 789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="head_of_department" class="form-label">
                                        <i class="fas fa-user-tie me-1"></i>
                                        Head of Department
                                    </label>
                                    <input type="text" class="form-control @error('head_of_department') is-invalid @enderror" 
                                           id="head_of_department" name="head_of_department" value="{{ old('head_of_department') }}"
                                           placeholder="Enter head of department">
                                    @error('head_of_department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>
                                        Department Email
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="department@hospital.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-image me-1"></i>
                                        Department Image
                                    </label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">Upload an image for the department (JPG, PNG, GIF - Max: 2MB)</div>
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
                                   id="location" name="location" value="{{ old('location') }}"
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
                        <h4 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Description
                        </h4>
                        <small class="opacity-75">Additional department details and notes</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-audio-description me-1"></i>
                                Description *
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="Enter department description" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="services" class="form-label">
                                <i class="fas fa-concierge-bell me-1"></i>
                                Services Offered
                            </label>
                            <div id="services-container">
                                @php
                                    $oldServices = old('services', ['']);
                                    if (is_string($oldServices)) {
                                        $oldServices = [$oldServices];
                                    }
                                @endphp
                                @foreach($oldServices as $index => $service)
                                <div class="service-item mb-2 d-flex align-items-center">
                                    <input type="text" class="form-control me-2" 
                                           name="services[]" 
                                           value="{{ $service }}" 
                                           placeholder="Enter service name">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-service" 
                                            {{ $index === 0 ? 'style=display:none;' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-service">
                                <i class="fas fa-plus me-1"></i> Add Service
                            </button>
                            @error('services')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('services.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="operating_hours" class="form-label">
                                <i class="fas fa-clock me-1"></i>
                                Operating Hours
                            </label>
                            <input type="text" class="form-control @error('operating_hours') is-invalid @enderror" 
                                   id="operating_hours" name="operating_hours" value="{{ old('operating_hours') }}" 
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
                            <i class="fas fa-save me-2"></i>
                            Create Department
                        </button>
                        <a href="{{ contextRoute('departments.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6>
                    <i class="fas fa-info-circle me-2"></i>
                    Department Creation Guidelines
                </h6>
                <ul>
                    <li>All fields marked with * are required</li>
                    <li>Department code should be unique and meaningful</li>
                    <li>Ensure contact information is accurate</li>
                    <li>Department head should have appropriate qualifications</li>
                </ul>
            </div>

            <div class="info-card">
                <h6>
                    <i class="fas fa-lightbulb me-2"></i>
                    Best Practices
                </h6>
                <ul>
                    <li>Provide clear and concise department descriptions</li>
                    <li>Maintain updated service listings</li>
                    <li>Set operating hours that reflect department functionality</li>
                    <li>Ensure all records comply with hospital standards</li>
                </ul>
            </div>

            <div class="info-card">
                <h6>
                    <i class="fas fa-clock me-2"></i>
                    Quick Actions
                </h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>
                        View All Departments
                    </a>
                    <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Clinics List
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
    $('#createDepartmentForm').on('submit', function(e) {
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
        $(this).after('<small class="text-muted char-counter">0/' + maxLength + ' characters</small>');
        
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

    // Dynamic services management
    $('#add-service').on('click', function() {
        const serviceItem = $('<div class="service-item mb-2 d-flex align-items-center">');
        serviceItem.html(`
            <input type="text" class="form-control me-2" 
                   name="services[]" 
                   placeholder="Enter service name">
            <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                <i class="fas fa-trash"></i>
            </button>
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

@push('styles')
<style>
    .section-header {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 10px;
    }
    
    .section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .section-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }
    
    .section-body {
        margin-bottom: 2rem;
    }
    
    .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.7rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        transform: translateY(-1px);
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .btn {
        border-radius: 8px;
        padding: 0.7rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    
    .info-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .info-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .info-card-title {
        margin: 0;
        font-size: 1rem;
        color: #495057;
        font-weight: 600;
    }
    
    .info-card-body {
        padding: 1rem;
    }
    
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-list li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f3f4;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .info-list li:last-child {
        border-bottom: none;
    }
    
    .status-item {
        margin-bottom: 0.8rem;
    }
    
    .status-label {
        font-weight: 600;
        color: #495057;
    }
    
    .status-description {
        color: #6c757d;
        font-size: 0.85rem;
        display: block;
        margin-top: 0.2rem;
    }
    
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: none;
        padding: 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .alert {
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .alert:last-child {
        margin-bottom: 0;
    }
    
    .btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-info:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

