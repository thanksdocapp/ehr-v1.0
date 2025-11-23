@extends('admin.layouts.app')

@section('title', 'Create Homepage Section')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('homepage-sections.index') }}">Homepage Sections</a></li>
    <li class="breadcrumb-item active">Create Section</li>
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

.color-preview {
    width: 40px;
    height: 40px;
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.color-preview:hover {
    border-color: #1cc88a;
    transform: scale(1.1);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-plus me-2 text-primary"></i>Create Homepage Section</h1>
        <p class="page-subtitle text-muted">Add a new section to your homepage</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createHomepageSectionForm" method="POST" action="{{ contextRoute('homepage-sections.store') }}">
                @csrf
                
                <!-- Section Content -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Section Content</h4>
                        <small class="opacity-75">Section text content and messaging</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="section_name" class="form-label">
                                <i class="fas fa-tag me-1"></i>Section Name *
                            </label>
                            <input type="text" class="form-control @error('section_name') is-invalid @enderror" 
                                   id="section_name" name="section_name" value="{{ old('section_name') }}" 
                                   placeholder="Enter unique section name (e.g., hero, services, about)" required>
                            <div class="form-help">Unique identifier for this section (no spaces, lowercase recommended)</div>
                            @error('section_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Title *
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Enter section title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="subtitle" class="form-label">
                                <i class="fas fa-text-height me-1"></i>Subtitle
                            </label>
                            <textarea class="form-control @error('subtitle') is-invalid @enderror" 
                                      id="subtitle" name="subtitle" rows="3" 
                                      placeholder="Enter section subtitle">{{ old('subtitle') }}</textarea>
                            <div class="form-help">Brief subtitle or tagline for the section</div>
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Enter section description">{{ old('description') }}</textarea>
                            <div class="form-help">Detailed description of the section content</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Display Settings</h4>
                        <small class="opacity-75">Section appearance and ordering</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order" class="form-label">
                                        <i class="fas fa-sort me-1"></i>Sort Order
                                    </label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" 
                                           min="1" placeholder="1">
                                    <div class="form-help">Lower numbers appear first</div>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active Section
                                        </label>
                                    </div>
                                    <div class="form-help">Enable this section to display on the homepage</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Section
                        </button>
                        <a href="{{ contextRoute('homepage-sections.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Section Guidelines</h6>
                <ul>
                    <li>Title should be unique and descriptive</li>
                    <li>Use clear language for subtitles or descriptions</li>
                    <li>Set a correct order for homepage display</li>
                    <li>Mark sections as active to display them</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Design Tips</h6>
                <ul>
                    <li>Maintain consistency in section styling</li>
                    <li>Use concise and engaging text</li>
                    <li>Review sections on multiple devices</li>
                    <li>Ensure all fields are filled appropriately</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('homepage-sections.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Sections
                    </a>
                    <a href="{{ contextRoute('homepage-sections.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Sections
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
    $('#title').on('input', function() {
        const title = $(this).val() || 'Sample Section Title';
        $('#previewTitle').text(title);
    });

    $('#subtitle').on('input', function() {
        const subtitle = $(this).val();
        $('#previewSubtitle').text(subtitle).show();
    });

    $('#description').on('input', function() {
        const description = $(this).val() || 'Sample description content.';
        $('#previewDescription').text(description);
    });
});
</script>
@endpush

