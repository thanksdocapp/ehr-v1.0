@extends('admin.layouts.app')

@section('title', 'Assign Service to Doctor')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold">Assign Service to Doctor</h4>
                    <small class="text-muted">Service: <strong>{{ $bookingService->name }}</strong></small>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.booking-services.store-doctor-assignment', $bookingService) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="doctor_id" class="form-label fw-semibold">Select Doctor <span class="text-danger">*</span></label>
                            <select class="form-select @error('doctor_id') is-invalid @enderror" 
                                    id="doctor_id" 
                                    name="doctor_id" 
                                    required>
                                <option value="">Choose a doctor...</option>
                                @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" 
                                        {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}
                                        {{ in_array($doctor->id, $assignedDoctorIds) ? 'data-assigned="true"' : '' }}>
                                    Dr. {{ $doctor->full_name }} 
                                    @if($doctor->specialization)
                                    - {{ $doctor->specialization }}
                                    @endif
                                    @if(in_array($doctor->id, $assignedDoctorIds))
                                    (Already assigned)
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="custom_price" class="form-label fw-semibold">Custom Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" 
                                               class="form-control @error('custom_price') is-invalid @enderror" 
                                               id="custom_price" 
                                               name="custom_price" 
                                               value="{{ old('custom_price', $bookingService->default_price) }}" 
                                               step="0.01" 
                                               min="0">
                                    </div>
                                    @error('custom_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty to use default: £{{ number_format($bookingService->default_price ?? 0, 2) }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="custom_duration_minutes" class="form-label fw-semibold">Custom Duration (minutes)</label>
                                    <input type="number" 
                                           class="form-control @error('custom_duration_minutes') is-invalid @enderror" 
                                           id="custom_duration_minutes" 
                                           name="custom_duration_minutes" 
                                           value="{{ old('custom_duration_minutes', $bookingService->default_duration_minutes) }}" 
                                           min="5" 
                                           max="480">
                                    @error('custom_duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Default: {{ $bookingService->default_duration_minutes }} minutes</small>
                                </div>
                            </div>
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
                                    Active (Service will be available for this doctor)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.booking-services.show', $bookingService) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Assign Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

