@extends('admin.layouts.app')

@section('title', 'Edit About Statistic')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('about-stats.index') }}">About Statistics</a></li>
    <li class="breadcrumb-item active">Edit Statistic</li>
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
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit About Statistic</h1>
        <p class="page-subtitle text-muted">Update statistic details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editAboutStatForm" method="POST" action="{{ contextRoute('about-stats.update', $aboutStat) }}">
                @csrf
                @method('PUT')
                
                <!-- Statistic Content -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Statistic Content</h4>
                        <small class="opacity-75">Update statistic content and appearance</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Title *
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $aboutStat->title) }}" 
                                   placeholder="Enter statistic title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="value" class="form-label">
                                <i class="fas fa-hashtag me-1"></i>Value *
                            </label>
                            <input type="text" class="form-control @error('value') is-invalid @enderror" 
                                   id="value" name="value" value="{{ old('value', $aboutStat->value) }}" 
                                   placeholder="Enter statistic value" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="icon" class="form-label">
                                <i class="fas fa-icons me-1"></i>Font Awesome Icon
                            </label>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon', $aboutStat->icon) }}" 
                                   placeholder="e.g., fas fa-heart">
                            <small class="form-text text-muted">Use Font Awesome classes (e.g., fas fa-heart).</small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="color" class="form-label">
                                <i class="fas fa-palette me-1"></i>Color
                            </label>
                            <input type="color" class="form-control-color @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', $aboutStat->color) }}">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Display Settings</h4>
                        <small class="opacity-75">Statistic appearance and ordering</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order" class="form-label">
                                        <i class="fas fa-sort me-1"></i>Sort Order
                                    </label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $aboutStat->sort_order) }}" 
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
                                               {{ old('is_active', $aboutStat->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-toggle-on me-1"></i>Active Statistic
                                        </label>
                                    </div>
                                    <div class="form-help">Enable this statistic to display</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Statistic Preview</label>
                            <div id="statistic-preview" class="text-center p-4 border rounded">
                                <div class="mb-3">
                                    <i id="previewIcon" class="{{ old('icon', $aboutStat->icon) }} fs-2" 
                                       style="color: {{ old('color', $aboutStat->color) }} !important;"></i>
                                </div>
                                <h3 id="previewValue" class="fw-bold mb-2" style="color: {{ old('color', $aboutStat->color) }} !important;">
                                    {{ old('value', $aboutStat->value) }}
                                </h3>
                                <h5 id="previewTitle" class="fw-semibold mb-1">
                                    {{ old('title', $aboutStat->title) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Statistic
                        </button>
                        <a href="{{ contextRoute('about-stats.index') }}" class="btn btn-secondary btn-lg">
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
                    <span class="status-badge {{ $aboutStat->is_active ? 'active' : 'inactive' }}">
                        {{ $aboutStat->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ formatDate($aboutStat->created_at) }}
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong> {{ formatDate($aboutStat->updated_at) }}
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Update Guidelines</h6>
                <ul>
                    <li>Ensure title is descriptive and clear</li>
                    <li>Update values with accurate numbers</li>
                    <li>Choose appropriate icons</li>
                    <li>Adjust sort order to change display sequence</li>
                    <li>Toggle active status to show/hide statistics</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('about-stats.show', $aboutStat) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View Statistic
                    </a>
                    <a href="{{ contextRoute('about-stats.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>All Statistics
                    </a>
                    <a href="{{ contextRoute('about-stats.index') }}" class="btn btn-outline-secondary btn-sm">
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
        const title = $(this).val() || '{{ $aboutStat->title }}';
        $('#previewTitle').text(title);
    });

    $('#value').on('input', function() {
        const value = $(this).val() || '{{ $aboutStat->value }}';
        $('#previewValue').text(value);
    });

    $('#icon').on('input', function() {
        const icon = $(this).val() || '{{ $aboutStat->icon }}';
        $('#previewIcon').attr('class', icon + ' fs-2').css('color', $('#color').val() + ' !important');
    });

    $('#color').on('input', function() {
        const color = $(this).val();
        $('#previewIcon').css('color', color + ' !important');
        $('#previewValue').css('color', color + ' !important');
    });
});
</script>
@endpush
