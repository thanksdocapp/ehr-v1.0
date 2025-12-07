@extends('admin.layouts.app')

@section('title', 'Document Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.document-templates.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection

@push('styles')
<style>
    .category-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .category-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .category-item {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border: 1px solid #e9ecef;
        cursor: grab;
        transition: all 0.2s;
    }
    .category-item:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    .category-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    .category-children {
        margin-left: 2rem;
        padding-left: 1rem;
        border-left: 2px solid #e9ecef;
    }
    .category-actions {
        opacity: 0;
        transition: opacity 0.2s;
    }
    .category-item:hover .category-actions {
        opacity: 1;
    }
    .color-option {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    .color-option:hover, .color-option.selected {
        transform: scale(1.2);
        border-color: #333;
    }
    .icon-option {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid transparent;
        background: #f8f9fa;
        transition: all 0.2s;
    }
    .icon-option:hover, .icon-option.selected {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    .stats-mini {
        display: flex;
        gap: 1.5rem;
    }
    .stat-mini-item {
        text-align: center;
    }
    .stat-mini-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }
    .stat-mini-label {
        font-size: 0.75rem;
        color: #666;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-folder-tree me-2 text-primary"></i>Document Categories
            </h1>
            <p class="text-muted mb-0">Organize your document templates into categories</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.document-settings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fas fa-plus me-2"></i>Add Category
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Categories List -->
        <div class="col-lg-8">
            <div class="card category-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2 text-muted"></i>All Categories
                        </h5>
                        <div class="stats-mini">
                            <div class="stat-mini-item">
                                <div class="stat-mini-value">{{ $categories->count() }}</div>
                                <div class="stat-mini-label">Total</div>
                            </div>
                            <div class="stat-mini-item">
                                <div class="stat-mini-value">{{ $categories->where('is_active', true)->count() }}</div>
                                <div class="stat-mini-label">Active</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($categories->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Categories Yet</h5>
                            <p class="text-muted mb-4">Create your first category to organize document templates.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="fas fa-plus me-2"></i>Create First Category
                            </button>
                        </div>
                    @else
                        <div id="categories-list">
                            @foreach($categories->where('parent_id', null) as $category)
                                @include('admin.document-settings.partials.category-item', ['category' => $category])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats & Help -->
        <div class="col-lg-4">
            <!-- Category Types -->
            <div class="card category-card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-tags me-2 text-muted"></i>By Type
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(102, 126, 234, 0.1);">
                            <span><i class="fas fa-envelope me-2 text-primary"></i>Letters</span>
                            <span class="badge bg-primary">{{ $categories->where('type', 'letter')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(17, 153, 142, 0.1);">
                            <span><i class="fas fa-clipboard-list me-2" style="color: #11998e;"></i>Forms</span>
                            <span class="badge" style="background: #11998e;">{{ $categories->where('type', 'form')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(108, 117, 125, 0.1);">
                            <span><i class="fas fa-folder me-2 text-secondary"></i>Both</span>
                            <span class="badge bg-secondary">{{ $categories->whereNull('type')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card category-card">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Tips
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Drag and drop categories to reorder them</li>
                        <li class="mb-2">Create sub-categories for better organization</li>
                        <li class="mb-2">Use colors and icons to visually distinguish categories</li>
                        <li class="mb-2">Inactive categories hide their templates from selection</li>
                        <li>Categories can be type-specific (letters only, forms only, or both)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="categoryForm" method="POST" action="{{ route('admin.document-settings.categories.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-folder-plus me-2"></i>Add Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="categoryName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" id="categoryType">
                                <option value="">Both (Letters & Forms)</option>
                                <option value="letter">Letters Only</option>
                                <option value="form">Forms Only</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="categoryDescription" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Parent Category</label>
                            <select class="form-select" name="parent_id" id="categoryParent">
                                <option value="">None (Root Category)</option>
                                @foreach($categories->where('parent_id', null) as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="categoryActive" value="1" checked>
                                <label class="form-check-label" for="categoryActive">Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Color</label>
                            <div class="d-flex gap-2 flex-wrap" id="colorPicker">
                                @php
                                    $colors = ['#667eea', '#11998e', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b', '#fa709a', '#fee140', '#f6d365', '#a18cd1', '#fbc2eb', '#ff9a9e', '#fad0c4', '#96e6a1', '#d4fc79', '#84fab0', '#8fd3f4'];
                                @endphp
                                @foreach($colors as $color)
                                    <div class="color-option" style="background: {{ $color }};" data-color="{{ $color }}"></div>
                                @endforeach
                            </div>
                            <input type="hidden" name="color" id="categoryColor" value="#667eea">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Icon</label>
                            <div class="d-flex gap-2 flex-wrap" id="iconPicker">
                                @php
                                    $icons = ['fa-folder', 'fa-file-alt', 'fa-file-medical', 'fa-notes-medical', 'fa-clipboard-list', 'fa-prescription', 'fa-heartbeat', 'fa-stethoscope', 'fa-user-md', 'fa-hospital', 'fa-ambulance', 'fa-pills', 'fa-syringe', 'fa-microscope', 'fa-dna', 'fa-x-ray', 'fa-tooth', 'fa-brain', 'fa-lungs', 'fa-bone', 'fa-hand-holding-medical', 'fa-procedures', 'fa-diagnoses', 'fa-file-signature'];
                                @endphp
                                @foreach($icons as $icon)
                                    <div class="icon-option" data-icon="{{ $icon }}">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="icon" id="categoryIcon" value="fa-folder">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    This will also delete all sub-categories. Templates will be moved to uncategorized.
                </p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Color picker
    $('.color-option').on('click', function() {
        $('.color-option').removeClass('selected');
        $(this).addClass('selected');
        $('#categoryColor').val($(this).data('color'));
    });

    // Icon picker
    $('.icon-option').on('click', function() {
        $('.icon-option').removeClass('selected');
        $(this).addClass('selected');
        $('#categoryIcon').val($(this).data('icon'));
    });

    // Set default selections
    $('.color-option[data-color="#667eea"]').addClass('selected');
    $('.icon-option[data-icon="fa-folder"]').addClass('selected');

    // Edit category
    window.editCategory = function(id) {
        $.get(`{{ url('admin/document-settings/categories') }}/${id}/edit`, function(data) {
            $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Edit Category');
            $('#categoryForm').attr('action', `{{ url('admin/document-settings/categories') }}/${id}`);
            $('#formMethod').val('PUT');

            $('#categoryName').val(data.name);
            $('#categoryDescription').val(data.description);
            $('#categoryType').val(data.type || '');
            $('#categoryParent').val(data.parent_id || '');
            $('#categoryActive').prop('checked', data.is_active);

            // Set color
            $('#categoryColor').val(data.color || '#667eea');
            $('.color-option').removeClass('selected');
            $(`.color-option[data-color="${data.color}"]`).addClass('selected');

            // Set icon
            $('#categoryIcon').val(data.icon || 'fa-folder');
            $('.icon-option').removeClass('selected');
            $(`.icon-option[data-icon="${data.icon}"]`).addClass('selected');

            $('#categoryModal').modal('show');
        });
    };

    // Delete category
    window.deleteCategory = function(id, name) {
        $('#deleteCategoryName').text(name);
        $('#deleteForm').attr('action', `{{ url('admin/document-settings/categories') }}/${id}`);
        $('#deleteModal').modal('show');
    };

    // Reset modal on close
    $('#categoryModal').on('hidden.bs.modal', function() {
        $('#modalTitle').html('<i class="fas fa-folder-plus me-2"></i>Add Category');
        $('#categoryForm').attr('action', '{{ route("admin.document-settings.categories.store") }}');
        $('#formMethod').val('POST');
        $('#categoryForm')[0].reset();

        $('.color-option').removeClass('selected');
        $('.color-option[data-color="#667eea"]').addClass('selected');
        $('#categoryColor').val('#667eea');

        $('.icon-option').removeClass('selected');
        $('.icon-option[data-icon="fa-folder"]').addClass('selected');
        $('#categoryIcon').val('fa-folder');
    });

    // Drag and drop reordering (simplified)
    let draggedItem = null;

    $('.category-item').attr('draggable', true);

    $(document).on('dragstart', '.category-item', function(e) {
        draggedItem = this;
        $(this).addClass('dragging');
    });

    $(document).on('dragend', '.category-item', function(e) {
        $(this).removeClass('dragging');
    });

    $(document).on('dragover', '.category-item', function(e) {
        e.preventDefault();
    });

    $(document).on('drop', '.category-item', function(e) {
        e.preventDefault();
        if (draggedItem !== this) {
            const orderedIds = [];
            $('#categories-list .category-item').each(function() {
                orderedIds.push($(this).data('id'));
            });

            // Save order via AJAX
            $.post('{{ route("admin.document-settings.categories.reorder") }}', {
                _token: '{{ csrf_token() }}',
                order: orderedIds
            });
        }
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>
@endpush
