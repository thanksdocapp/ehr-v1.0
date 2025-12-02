@extends('layouts.staff')

@section('title', 'Edit Service Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">Edit Service Settings</h2>
                    <p class="text-muted mb-0">Customize pricing and duration for: {{ $bookingService->name }}</p>
                </div>
                <div>
                    <a href="{{ route('staff.doctor-services.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Services
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">Service Information</h5>
                </div>
                <div class="card-body">
                    <!-- Service Details (Read-only) -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="fw-semibold mb-3">Global Service Details</h6>
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
                            @if($bookingService->description)
                            <div class="col-12 mt-2">
                                <small class="text-muted d-block">Description</small>
                                <p class="mb-0">{{ $bookingService->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form action="{{ route('staff.doctor-services.update', $bookingService) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-semibold mb-3">Your Custom Settings</h6>

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

