@extends('admin.layouts.app')

@section('page-title', 'Custom Menu Items')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Custom Menu Items</h1>
                <div>
                    <a href="{{ route('admin.custom-menu-items.create', ['type' => $menuType]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Link
                    </a>
                </div>
            </div>
            <p class="text-muted">Manage custom menu links for {{ ucfirst($menuType) }} sidebar</p>
        </div>
    </div>

    <!-- Menu Type Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $menuType === 'staff' ? 'active' : '' }}" href="{{ route('admin.custom-menu-items.index', ['type' => 'staff']) }}">
                <i class="fas fa-user-md me-2"></i>Staff Sidebar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $menuType === 'admin' ? 'active' : '' }}" href="{{ route('admin.custom-menu-items.index', ['type' => 'admin']) }}">
                <i class="fas fa-user-shield me-2"></i>Admin Sidebar
            </a>
        </li>
    </ul>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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

    @if($menuItems->isEmpty())
        <div class="admin-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-link fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No custom menu items</h5>
                <p class="text-muted mb-4">Add custom links to appear in the {{ ucfirst($menuType) }} sidebar</p>
                <a href="{{ route('admin.custom-menu-items.create', ['type' => $menuType]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add First Link
                </a>
            </div>
        </div>
    @else
        <div class="admin-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Order</th>
                                <th>Label</th>
                                <th>URL</th>
                                <th>Icon</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menuItems as $item)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $item->order }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item->label }}</strong>
                                    @if($item->description)
                                        <br><small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $item->url }}" target="{{ $item->target }}" rel="noopener noreferrer" class="text-truncate d-inline-block" style="max-width: 300px; cursor: pointer; color: #0d6efd; text-decoration: underline;">
                                        {{ $item->url }}
                                        <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                </td>
                                <td>
                                    @if($item->icon)
                                        <i class="fas {{ $item->icon }}"></i>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->target === '_blank')
                                        <span class="badge bg-info">New Tab</span>
                                    @else
                                        <span class="badge bg-secondary">Same Tab</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.custom-menu-items.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.role-menu-visibility.index', ['type' => $item->menu_type, 'role' => 'doctor']) }}" class="btn btn-sm btn-outline-info" title="Manage Visibility">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.custom-menu-items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this custom menu item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Ensure all URL links in the table are clickable
    $('.table a[href^="http"]').on('click', function(e) {
        // Allow default link behavior - open in new/same tab based on target
        // Don't prevent default unless it's a special case
        const $link = $(this);
        const href = $link.attr('href');
        const target = $link.attr('target') || '_self';
        
        // Verify the link has a valid URL
        if (href && (href.startsWith('http://') || href.startsWith('https://'))) {
            // Link is valid, allow normal behavior
            console.log('Opening link:', href, 'in', target);
        } else {
            // Invalid URL, prevent navigation
            e.preventDefault();
            alert('Invalid URL: ' + href);
        }
    });
    
    // Make sure action buttons work
    $('.btn-group a[href]').on('click', function(e) {
        const href = $(this).attr('href');
        if (href && href !== '#' && !href.startsWith('javascript:')) {
            // Valid link, allow navigation
            console.log('Navigating to:', href);
        }
    });
});
</script>
@endpush
@endsection

