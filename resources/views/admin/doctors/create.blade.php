@extends('admin.layouts.app')

@section('title', 'Add New Doctor')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('doctors.index') }}">Doctors</a></li>
    <li class="breadcrumb-item active">Add New Doctor</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div>
                <h1 class="modern-page-title">Add New Doctor</h1>
                <p class="modern-page-subtitle">Register a new doctor in the system</p>
            </div>
        </div>
    </div>
    
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-user-md"></i>Doctor Information
            </h5>
        </div>
        <div class="modern-card-body">

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
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    border-bottom: 2px solid #e2e8f0;
}

.form-section-header h4,
.form-section-header h5 {
    color: #1a202c;
    font-weight: 700;
}

.form-section-header i {
    color: #1a202c;
}

.form-section-header small {
    color: #4a5568;
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

.form-control {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
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

.photo-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed #e3e6f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fc;
    margin-bottom: 1rem;
}

.photo-preview img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
}
</style>
@endpush

    <div class="row">
        <div class="col-lg-8">
            <form id="createDoctorForm" action="{{ contextRoute('doctors.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h4>
                        <small class="opacity-75">Basic personal details and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>First Name *
                                    </label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" 
                                           placeholder="Enter first name" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Last Name *
                                    </label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" 
                                           placeholder="Enter last name" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="doctor@hospital.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="+000 123 456 789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="title" value="Dr.">

                        <div class="form-group">
                            <label for="photo" class="form-label">
                                <i class="fas fa-camera me-1"></i>Doctor Photo
                            </label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" name="photo" accept=".jpg,.jpeg,.png,.gif,.webp">
                            <div class="form-help">Upload a professional photo (JPEG, PNG, JPG, GIF - Max: 2MB)</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Professional Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Professional Information</h4>
                        <small class="opacity-75">Medical qualifications and specialisation details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="specialization" class="form-label">
                                        <i class="fas fa-user-md me-1"></i>Specialisation *
                                    </label>
                                    <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                           id="specialization" name="specialization" value="{{ old('specialization') }}" 
                                           placeholder="Enter specialization" required>
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>Primary Department *
                                    </label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id" required>
                                        <option value="">Select Primary Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">The primary department for this doctor</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_ids" class="form-label">
                                        <i class="fas fa-building me-1"></i>Additional Departments
                                    </label>
                                    <select class="form-control @error('department_ids') is-invalid @enderror" 
                                            id="department_ids" name="department_ids[]" multiple size="4">
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ in_array($department->id, old('department_ids', [])) ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple departments</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="languages" class="form-label">
                                <i class="fas fa-language me-1"></i>Languages
                            </label>
                            <div id="languages-container">
                                @if(old('languages'))
                                    @foreach(old('languages') as $language)
                                        <div class="input-group mb-2 language-item">
                                            <input type="text" class="form-control" name="languages[]" 
                                                   value="{{ $language }}" placeholder="Enter language">
                                            <button type="button" class="btn btn-outline-danger remove-language">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 language-item">
                                        <input type="text" class="form-control" name="languages[]" 
                                               placeholder="Enter language">
                                        <button type="button" class="btn btn-outline-danger remove-language">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-language">
                                <i class="fas fa-plus"></i> Add Language
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="specialties" class="form-label">
                                <i class="fas fa-star me-1"></i>Specialties
                            </label>
                            <div id="specialties-container">
                                @if(old('specialties'))
                                    @foreach(old('specialties') as $specialty)
                                        <div class="input-group mb-2 specialty-item">
                                            <input type="text" class="form-control" name="specialties[]" 
                                                   value="{{ $specialty }}" placeholder="Enter specialty">
                                            <button type="button" class="btn btn-outline-danger remove-specialty">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 specialty-item">
                                        <input type="text" class="form-control" name="specialties[]" 
                                               placeholder="Enter specialty">
                                        <button type="button" class="btn btn-outline-danger remove-specialty">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-specialty">
                                <i class="fas fa-plus"></i> Add Specialty
                            </button>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_available_online" name="is_available_online" value="1" 
                                               {{ old('is_available_online') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_available_online">
                                            <i class="fas fa-video me-1"></i>Available for Online Consultation
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_featured" name="is_featured" value="1" 
                                               {{ old('is_featured') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">
                                            <i class="fas fa-star me-1"></i>Featured Doctor
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active
                                        </label>
                                    </div>
                                    <div class="form-help">Check to make this doctor active</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Save Doctor
                        </button>
                        <a href="{{ contextRoute('doctors.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Doctor Creation Guidelines</h6>
                <ul>
                    <li>Fill in all required fields marked with *</li>
                    <li>Upload a professional photo for better patient trust</li>
                    <li>Title is automatically set to "Dr."</li>
                    <li>Add languages and specialties for better patient matching</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Photo Preview</h6>
                <div class="photo-preview text-center" id="photo-preview">
                    <i class="fas fa-user-md text-muted fa-3x"></i>
                    <p class="text-muted mt-2">No photo selected</p>
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security</h6>
                <ul>
                    <li><strong>Data Protection:</strong> All information is securely stored</li>
                    <li><strong>Access Control:</strong> Only authorized staff can access</li>
                    <li><strong>Audit Trail:</strong> All changes are tracked</li>
                    <li><strong>Compliance:</strong> GDPR compliant system</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('doctors.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Doctors List
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
    // Add language functionality
    $('#add-language').click(function() {
        const languageHtml = `
            <div class="input-group mb-2 language-item">
                <input type="text" class="form-control" name="languages[]" placeholder="Enter language">
                <button type="button" class="btn btn-outline-danger remove-language">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        $('#languages-container').append(languageHtml);
    });

    // Remove language functionality
    $(document).on('click', '.remove-language', function() {
        if ($('.language-item').length > 1) {
            $(this).closest('.language-item').remove();
        }
    });

    // Add specialty functionality
    $('#add-specialty').click(function() {
        const specialtyHtml = `
            <div class="input-group mb-2 specialty-item">
                <input type="text" class="form-control" name="specialties[]" placeholder="Enter specialty">
                <button type="button" class="btn btn-outline-danger remove-specialty">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        $('#specialties-container').append(specialtyHtml);
    });

    // Remove specialty functionality
    $(document).on('click', '.remove-specialty', function() {
        if ($('.specialty-item').length > 1) {
            $(this).closest('.specialty-item').remove();
        }
    });

    // Photo preview
    $('#photo').change(function(e) {
        const file = e.target.files[0];
        const preview = $('#photo-preview');
        
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
                <i class="fas fa-user-md text-muted fa-3x"></i>
                <p class="text-muted mt-2">No photo selected</p>
            `);
        }
    });

    // Form validation
    $('#createDoctorForm').submit(function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['first_name', 'last_name', 'specialization', 'department_id'];
        
        requiredFields.forEach(function(field) {
            const input = $('#' + field);
            if (!input.val()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush
