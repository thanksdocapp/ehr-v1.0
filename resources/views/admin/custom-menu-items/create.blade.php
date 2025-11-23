@extends('admin.layouts.app')

@section('page-title', 'Add Custom Menu Item')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Add Custom Menu Item</h1>
                <a href="{{ route('admin.custom-menu-items.index', ['type' => $menuType]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
            <p class="text-muted">Add a custom link to the {{ ucfirst($menuType) }} sidebar</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="mb-0">Menu Item Details</h5>
                </div>
                <div class="card-body">
                    <form id="create-form" action="{{ route('admin.custom-menu-items.store') }}" method="POST">
                        @csrf
                        
                        <input type="hidden" name="menu_type" value="{{ $menuType }}">

                        <div class="mb-3">
                            <label for="label" class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label') }}" required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">The text to display in the sidebar</small>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url') }}" placeholder="https://example.com" required>
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">The full URL to link to (must start with http:// or https://)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icon</label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', 'fa-external-link-alt') }}" placeholder="fa-external-link-alt">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Font Awesome icon class (e.g., fa-link, fa-external-link-alt)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="target" class="form-label">Open In <span class="text-danger">*</span></label>
                                    <select class="form-select @error('target') is-invalid @enderror" id="target" name="target" required>
                                        <option value="_blank" {{ old('target', '_blank') === '_blank' ? 'selected' : '' }}>New Tab</option>
                                        <option value="_self" {{ old('target') === '_self' ? 'selected' : '' }}>Same Tab</option>
                                    </select>
                                    @error('target')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order" class="form-label">Order</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', 0) }}" min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Lower numbers appear first</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Only active items are displayed</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional description (not displayed in sidebar)</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.custom-menu-items.index', ['type' => $menuType]) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Menu Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="mb-0">Preview</h5>
                </div>
                <div class="card-body">
                    <div class="nav-section">
                        <div class="nav-title">Quick Links</div>
                        <div class="nav-item">
                            <a href="#" class="nav-link" id="preview-link">
                                <i class="nav-icon fas" id="preview-icon">fa-external-link-alt</i>
                                <span class="nav-text" id="preview-label">Menu Item Label</span>
                                <i class="fas fa-external-link-alt ms-auto" style="font-size: 0.75rem; opacity: 0.7;" id="preview-target-icon"></i>
                            </a>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted">This is how the menu item will appear in the sidebar</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live preview
    document.getElementById('label').addEventListener('input', function() {
        document.getElementById('preview-label').textContent = this.value || 'Menu Item Label';
    });

    document.getElementById('icon').addEventListener('input', function() {
        const iconEl = document.getElementById('preview-icon');
        iconEl.className = 'nav-icon fas ' + (this.value || 'fa-external-link-alt');
    });

    document.getElementById('target').addEventListener('change', function() {
        const targetIcon = document.getElementById('preview-target-icon');
        if (this.value === '_blank') {
            targetIcon.style.display = 'inline-block';
        } else {
            targetIcon.style.display = 'none';
        }
    });
</script>
@endpush
@endsection

