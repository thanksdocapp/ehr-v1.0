@extends('admin.layouts.app')

@section('title', 'Edit Homepage Section')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('homepage-sections.index') }}">Homepage Sections</a></li>
    <li class="breadcrumb-item active">Edit Section</li>
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
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit Homepage Section</h1>
        <p class="page-subtitle text-muted">Update section details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editHomepageSectionForm" method="POST" action="{{ contextRoute('homepage-sections.update', $homepageSection) }}">
                @csrf
                @method('PUT')
                
                <!-- Section Content -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Section Content</h4>
                        <small class="opacity-75">Update section text content and messaging</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="section_name" class="form-label">
                                <i class="fas fa-tag me-1"></i>Section Name *
                            </label>
                            <input type="text" class="form-control @error('section_name') is-invalid @enderror" 
                                   id="section_name" name="section_name" value="{{ old('section_name', $homepageSection->section_name) }}" 
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
                                   id="title" name="title" value="{{ old('title', $homepageSection->title) }}" 
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
                                      placeholder="Enter section subtitle">{{ old('subtitle', $homepageSection->subtitle) }}</textarea>
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
                                      placeholder="Enter section description">{{ old('description', $homepageSection->description) }}</textarea>
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
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $homepageSection->sort_order) }}" 
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
                                               {{ old('is_active', $homepageSection->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active Section
                                        </label>
                                    </div>
                                    <div class="form-help">Enable this section to display on the homepage</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Section Preview</label>
                            <div id="section-preview" class="text-center p-4 border rounded">
                                <h5 id="previewTitle" class="fw-bold mb-3">
                                    {{ old('title', $homepageSection->title) }}
                                </h5>
                                <h6 id="previewSubtitle" class="text-muted mb-2" style="display: {{ old('subtitle', $homepageSection->subtitle) ? 'block' : 'none' }}">
                                    {{ old('subtitle', $homepageSection->subtitle) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Section
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
                <h6><i class="fas fa-info-circle me-2"></i>Current Section Status</h6>
                <div class="mb-3">
                    <strong>Status:</strong> 
                    <span class="status-badge {{ $homepageSection->is_active ? 'active' : 'inactive' }}">
                        {{ $homepageSection->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ formatDate($homepageSection->created_at) }}
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong> {{ formatDate($homepageSection->updated_at) }}
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Update Guidelines</h6>
                <ul>
                    <li>Ensure title remains unique and descriptive</li>
                    <li>Update subtitles only if necessary</li>
                    <li>Adjust sort order to change section sequence</li>
                    <li>Toggle active status to show/hide sections</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('homepage-sections.show', $homepageSection) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View Section
                    </a>
                    <a href="{{ contextRoute('homepage-sections.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>All Sections
                    </a>
                    <a href="{{ contextRoute('homepage-sections.index') }}" class="btn btn-outline-secondary btn-sm">
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
    // Live preview updates
    $('#title').on('input', function() {
        const title = $(this).val() || '{{ $homepageSection->title }}';
        $('#previewTitle').text(title);
    });

    $('#subtitle').on('input', function() {
        const subtitle = $(this).val();
        if (subtitle) {
            $('#previewSubtitle').text(subtitle).show();
        } else {
            $('#previewSubtitle').hide();
        }
    });
});
</script>
@endpush
