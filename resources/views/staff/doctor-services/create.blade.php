@extends('layouts.doctor')

@section('title', 'Create Service')
@section('page-title', 'Create New Service')
@section('page-subtitle', 'Add a new service to your practice')

@section('content')
<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div>
            <a href="{{ route('staff.doctor-services.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Services
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Please correct the errors below.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Service Details
                    </h5>
                </div>

                <div class="doctor-card-body">
                    <form action="{{ route('staff.doctor-services.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="default_duration_minutes" class="form-label fw-semibold">Duration (minutes) <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   class="form-control @error('default_duration_minutes') is-invalid @enderror"
                                                   id="default_duration_minutes"
                                                   name="default_duration_minutes"
                                                   value="{{ old('default_duration_minutes', 30) }}"
                                                   min="5"
                                                   max="480"
                                                   required>
                                            @error('default_duration_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Minimum 5 minutes, maximum 480 minutes (8 hours)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="default_price" class="form-label fw-semibold">Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">£</span>
                                                <input type="number"
                                                       class="form-control @error('default_price') is-invalid @enderror"
                                                       id="default_price"
                                                       name="default_price"
                                                       value="{{ old('default_price') }}"
                                                       step="0.01"
                                                       min="0">
                                            </div>
                                            @error('default_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Leave empty for "Price on request"</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="consultation_type" class="form-label fw-semibold">Consultation Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('consultation_type') is-invalid @enderror"
                                            id="consultation_type"
                                            name="consultation_type"
                                            required>
                                        <option value="in_person" {{ old('consultation_type', 'in_person') == 'in_person' ? 'selected' : '' }}>In-Person Consultation</option>
                                        <option value="online" {{ old('consultation_type') == 'online' ? 'selected' : '' }}>Online Consultation</option>
                                    </select>
                                    @error('consultation_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">This determines how patients will book this service. Patients will see this type when booking.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="tags_input" class="form-label fw-semibold">Tags</label>
                                    <input type="text"
                                           class="form-control @error('tags_input') is-invalid @enderror"
                                           id="tags_input"
                                           name="tags_input"
                                           value="{{ old('tags_input') }}"
                                           placeholder="e.g., online, face_to_face, consultation">
                                    @error('tags_input')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Separate tags with commas (e.g., online, face_to_face)</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active (Service will be available for booking)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('staff.doctor-services.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
