@extends('admin.layouts.app')

@section('title', 'Edit Booking Service')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold">Edit Booking Service</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.booking-services.update', $bookingService) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $bookingService->name) }}" 
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
                                              rows="4">{{ old('description', $bookingService->description) }}</textarea>
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
                                                   value="{{ old('default_duration_minutes', $bookingService->default_duration_minutes) }}" 
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
                                            <label for="default_price" class="form-label fw-semibold">Default Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">£</span>
                                                <input type="number" 
                                                       class="form-control @error('default_price') is-invalid @enderror" 
                                                       id="default_price" 
                                                       name="default_price" 
                                                       value="{{ old('default_price', $bookingService->default_price) }}" 
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
                                    <label for="tags" class="form-label fw-semibold">Tags</label>
                                    <input type="text" 
                                           class="form-control @error('tags') is-invalid @enderror" 
                                           id="tags" 
                                           name="tags_input" 
                                           value="{{ old('tags_input', is_array($bookingService->tags) ? implode(', ', $bookingService->tags) : '') }}"
                                           placeholder="e.g., online, face_to_face, consultation">
                                    @error('tags')
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
                                               {{ old('is_active', $bookingService->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active (Service will be available for booking)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.booking-services.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tagsInput = document.getElementById('tags');
    
    // Convert comma-separated tags to array on form submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const tagsValue = tagsInput.value.trim();
        if (tagsValue) {
            const tagsArray = tagsValue.split(',').map(tag => tag.trim()).filter(tag => tag);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'tags';
            hiddenInput.value = JSON.stringify(tagsArray);
            this.appendChild(hiddenInput);
        }
    });
});
</script>
@endsection

