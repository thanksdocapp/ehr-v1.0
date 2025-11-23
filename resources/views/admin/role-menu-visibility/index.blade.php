@extends('admin.layouts.app')

@section('title', 'Role-Based Menu Visibility')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Role Menu Visibility</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Title -->
    <div class="page-title" style="margin-bottom: 15px;">
        <h1>Role-Based Menu Visibility</h1>
        <p class="page-subtitle">Configure which menu items are visible for each user role</p>
    </div>

    <!-- Filters -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.role-menu-visibility.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Menu Type:</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="admin" {{ $menuType === 'admin' ? 'selected' : '' }}>Admin Menu</option>
                        <option value="staff" {{ $menuType === 'staff' ? 'selected' : '' }}>Staff Menu</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">User Role:</label>
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        @foreach($availableRoles as $availableRole)
                            <option value="{{ $availableRole }}" {{ $role === $availableRole ? 'selected' : '' }}>
                                {{ ucfirst($availableRole) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="resetVisibility()">
                        <i class="fas fa-undo me-1"></i>Reset to Default
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Instructions -->
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            <strong>How to use:</strong> Select a role from the dropdown above to configure menu visibility for that role. 
            Use the toggle switches to show/hide menu items for the selected role. 
            Drag menu items to reorder them. Click "Save Visibility" to apply changes.
        </div>
    </div>

    <!-- Menu Items List -->
    <div class="admin-card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Menu Items for <strong>{{ ucfirst($role) }}</strong> Role ({{ ucfirst($menuType) }} Menu)
            </h5>
        </div>
        <div class="card-body">
            <form id="visibilityForm">
                <input type="hidden" name="role" value="{{ $role }}">
                <input type="hidden" name="menu_type" value="{{ $menuType }}">
                <div id="menuItemsList" class="menu-items-list">
                    @foreach($menuItems as $index => $item)
                    <div class="menu-item-draggable" data-key="{{ $item['menu_key'] }}">
                        <div class="menu-item-handle">
                            <i class="fas fa-grip-vertical text-muted me-3"></i>
                            @if(isset($item['icon']))
                                <i class="fas fa-{{ $item['icon'] }} text-primary me-3"></i>
                            @endif
                            <span class="menu-item-label">{{ $item['label'] }}</span>
                            @if(isset($item['is_custom']) && $item['is_custom'])
                                <span class="badge bg-info ms-2" title="Custom Menu Item">
                                    <i class="fas fa-link me-1"></i>Custom Link
                                </span>
                            @else
                                <span class="badge bg-secondary ms-2">{{ $item['menu_key'] }}</span>
                            @endif
                        </div>
                        <div class="menu-item-controls">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       id="visible_{{ $item['menu_key'] }}" 
                                       name="visible_{{ $item['menu_key'] }}" 
                                       {{ ($item['is_visible'] ?? true) ? 'checked' : '' }}
                                       onchange="updateHiddenField('{{ $item['menu_key'] }}', this.checked)">
                                <label class="form-check-label" for="visible_{{ $item['menu_key'] }}">
                                    Visible
                                </label>
                            </div>
                            <input type="hidden" name="menu_items[{{ $index }}][menu_key]" value="{{ $item['menu_key'] }}">
                            <input type="hidden" name="menu_items[{{ $index }}][is_visible]" 
                                   value="{{ ($item['is_visible'] ?? true) ? '1' : '0' }}" 
                                   id="hidden_visible_{{ $item['menu_key'] }}">
                            <input type="hidden" name="menu_items[{{ $index }}][order]" value="{{ $item['order'] ?? $index }}">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-secondary" onclick="location.reload()">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save me-1"></i>Save Visibility
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .menu-items-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .menu-item-draggable {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
        cursor: move;
    }

    .menu-item-draggable:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateX(5px);
    }

    .menu-item-draggable.sortable-ghost {
        opacity: 0.4;
    }

    .menu-item-handle {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .menu-item-label {
        font-weight: 500;
        color: #2c3e50;
    }

    .menu-item-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .form-check-switch {
        display: flex;
        align-items: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // Initialize SortableJS
    const menuList = document.getElementById('menuItemsList');
    const sortable = new Sortable(menuList, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        handle: '.menu-item-handle',
        onEnd: function(evt) {
            // Update order values after drag
            updateOrderValues();
        }
    });

    function updateOrderValues() {
        const items = document.querySelectorAll('.menu-item-draggable');
        items.forEach((item, index) => {
            const orderInput = item.querySelector('input[name*="[order]"]');
            if (orderInput) {
                orderInput.value = index;
            }
        });
    }

    function updateHiddenField(menuKey, isVisible) {
        const hiddenInput = document.getElementById('hidden_visible_' + menuKey);
        if (hiddenInput) {
            hiddenInput.value = isVisible ? '1' : '0';
        }
    }

    function resetVisibility() {
        if (confirm('Are you sure you want to reset visibility settings for {{ ucfirst($role) }} role to default? This action cannot be undone.')) {
            window.location.href = '{{ route("admin.role-menu-visibility.reset") }}?role={{ $role }}&menu_type={{ $menuType }}';
        }
    }

    // Form submission
    document.getElementById('visibilityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const menuItems = [];
        
        // Collect all menu items
        const items = document.querySelectorAll('.menu-item-draggable');
        items.forEach((item) => {
            const menuKey = item.dataset.key;
            const isVisible = document.getElementById('visible_' + menuKey)?.checked || false;
            const orderInput = item.querySelector('input[name*="[order]"]');
            const order = orderInput ? parseInt(orderInput.value) : 0;
            
            menuItems.push({
                menu_key: menuKey,
                is_visible: isVisible,
                order: order
            });
        });
        
        // Update form data
        formData.delete('menu_items[]');
        menuItems.forEach((item, index) => {
            formData.append(`menu_items[${index}][menu_key]`, item.menu_key);
            formData.append(`menu_items[${index}][is_visible]`, item.is_visible ? '1' : '0');
            formData.append(`menu_items[${index}][order]`, item.order);
        });
        
        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        fetch('{{ route("admin.role-menu-visibility.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while saving. Please try again.'
            });
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    });

    // Update order on page load
    updateOrderValues();
</script>
@endpush
@endsection

