@extends('layouts.doctor')

@section('title', 'Edit Service Settings')
@section('page-title', 'Edit Service Settings')
@section('page-subtitle', 'Customize pricing and duration for: {{ $bookingService->name }}')

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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-edit me-2 text-primary"></i>Service information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-4 p-3 bg-light rounded">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-cog me-1"></i>Global service details</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Service Name</small>
                                <strong>{{ $bookingService->name }}</strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Default Duration</small>
                                <strong>{{ $bookingService->default_duration_minutes ?? 60 }} minutes</strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Default Price</small>
                                <strong>£{{ number_format($bookingService->default_price ?? 0, 2) }}</strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Global Status</small>
                                @if($bookingService->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form action="{{ route('staff.doctor-services.update', $bookingService) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-semibold mb-3">Service Details</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="default_price" class="form-label">
                                    Default Price <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number"
                                           class="form-control @error('default_price') is-invalid @enderror"
                                           id="default_price"
                                           name="default_price"
                                           value="{{ old('default_price', $bookingService->default_price) }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                                <small class="text-muted">Base price for this service</small>
                                @error('default_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description <span class="text-muted">(optional)</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Enter service description...">{{ old('description', $bookingService->description) }}</textarea>
                            <small class="text-muted">This description will be shown to patients when booking</small>
                            @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <h6 class="fw-semibold mb-3 mt-4">Your Custom Settings</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="custom_price" class="form-label">
                                    Custom Price <span class="text-muted">(optional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number"
                                           class="form-control @error('custom_price') is-invalid @enderror"
                                           id="custom_price"
                                           name="custom_price"
                                           value="{{ old('custom_price', $override->custom_price) }}"
                                           step="0.01"
                                           min="0"
                                           placeholder="{{ number_format($bookingService->default_price ?? 0, 2) }}">
                                </div>
                                <small class="text-muted">Leave empty to use global default: £{{ number_format($bookingService->default_price ?? 0, 2) }}</small>
                                @error('custom_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="custom_duration_minutes" class="form-label">
                                    Custom Duration <span class="text-muted">(optional)</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('custom_duration_minutes') is-invalid @enderror"
                                           id="custom_duration_minutes"
                                           name="custom_duration_minutes"
                                           value="{{ old('custom_duration_minutes', $override->custom_duration_minutes) }}"
                                           min="1"
                                           placeholder="{{ $bookingService->default_duration_minutes ?? 60 }}">
                                    <span class="input-group-text">minutes</span>
                                </div>
                                <small class="text-muted">Leave empty to use global default: {{ $bookingService->default_duration_minutes ?? 60 }} minutes</small>
                                @error('custom_duration_minutes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="consultation_type" class="form-label">
                                Consultation Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('consultation_type') is-invalid @enderror"
                                    id="consultation_type"
                                    name="consultation_type"
                                    required>
                                <option value="in_person" {{ old('consultation_type', $override->consultation_type ?? 'in_person') == 'in_person' ? 'selected' : '' }}>In Person</option>
                                <option value="online" {{ old('consultation_type', $override->consultation_type ?? 'in_person') == 'online' ? 'selected' : '' }}>Online (Video)</option>
                                <option value="telephone" {{ old('consultation_type', $override->consultation_type ?? 'in_person') == 'telephone' ? 'selected' : '' }}>Telephone</option>
                            </select>
                            @error('consultation_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This determines how patients will book this service. Patients will see this type when booking.</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', $override->is_active ?? $bookingService->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active for my bookings</strong>
                                    <small class="text-muted d-block">When enabled, patients can book this service through your booking link</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('staff.doctor-services.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
