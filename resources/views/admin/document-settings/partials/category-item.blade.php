<div class="category-item" data-id="{{ $category->id }}">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="category-icon me-3" style="background: {{ $category->color }}20; color: {{ $category->color }};">
                <i class="fas {{ $category->icon ?? 'fa-folder' }}"></i>
            </div>
            <div>
                <h6 class="mb-0">
                    {{ $category->name }}
                    @if(!$category->is_active)
                        <span class="badge bg-secondary ms-2">Inactive</span>
                    @endif
                </h6>
                <div class="small text-muted">
                    @if($category->type === 'letter')
                        <i class="fas fa-envelope me-1"></i>Letters
                    @elseif($category->type === 'form')
                        <i class="fas fa-clipboard-list me-1"></i>Forms
                    @else
                        <i class="fas fa-file-alt me-1"></i>All Types
                    @endif
                    <span class="mx-2">•</span>
                    <span>{{ $category->templates_count ?? $category->templates()->count() }} templates</span>
                    @if($category->children->count() > 0)
                        <span class="mx-2">•</span>
                        <span>{{ $category->children->count() }} sub-categories</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="category-actions d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCategory({{ $category->id }})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</div>

@if($category->children->count() > 0)
    <div class="category-children">
        @foreach($category->children as $child)
            @include('admin.document-settings.partials.category-item', ['category' => $child])
        @endforeach
    </div>
@endif
