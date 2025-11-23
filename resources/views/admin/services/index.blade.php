@extends('admin.layouts.app')

@section('title', 'Services Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Services Management</h3>
                    <a href="{{ contextRoute('services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Service
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ contextRoute('services.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search services..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="department_id" class="form-select">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary me-2">Search</button>
                                    <a href="{{ contextRoute('services.index') }}" class="btn btn-outline-secondary">Clear</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Department</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($service->image)
                                                    <img src="{{ asset('storage/' . $service->image) }}" 
                                                         alt="{{ $service->name }}" 
                                                         class="me-3 rounded" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $service->name }}</div>
                                                    <small class="text-muted">{{ Str::limit($service->description, 80) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($service->department)
                                                <span class="badge bg-primary">{{ $service->department->name }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">No Department</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->price)
                                                ${{ number_format($service->price, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->duration)
                                                {{ $service->duration }} min
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm {{ $service->is_active ? 'btn-success' : 'btn-secondary' }} toggle-status" 
                                                    data-id="{{ $service->id }}" 
                                                    data-url="{{ contextRoute('services.toggle-status', $service) }}">
                                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ contextRoute('services.show', $service) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ contextRoute('services.edit', $service) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteService({{ $service->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No services found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $services->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status').click(function() {
        let button = $(this);
        let url = button.data('url');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.status) {
                        button.removeClass('btn-secondary').addClass('btn-success').text('Active');
                    } else {
                        button.removeClass('btn-success').addClass('btn-secondary').text('Inactive');
                    }
                    
                    // Show success message
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error updating service status');
            }
        });
    });

    // Delete confirmation - using comprehensive confirmation dialog
    window.deleteService = function(serviceId) {
        console.log('Delete service called with ID:', serviceId);
        
        // Prevent any default behavior if event exists
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Handle both sync and async confirm dialogs
        function handleConfirmation(confirmResult) {
            console.log('User confirmation result:', confirmResult);
            
            if (confirmResult === true) {
                console.log('User confirmed deletion, proceeding...');
                
                // Add a small delay to ensure the dialog is properly closed
                setTimeout(() => {
                    console.log('Creating form for deletion...');
                    
                    // Create a form to submit the DELETE request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/services/${serviceId}`;
                    form.style.display = 'none';
                    
                    // Add CSRF token - try multiple methods
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    
                    // Try to get CSRF token from meta tag or Laravel's global
                    let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                        csrfTokenValue = Laravel.csrfToken;
                    }
                    if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                        csrfTokenValue = window.Laravel.csrfToken;
                    }
                    
                    csrfToken.value = csrfTokenValue;
                    form.appendChild(csrfToken);
                    
                    console.log('CSRF token:', csrfTokenValue);
                    
                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Form children:', form.children);
                    
                    // Add form to document and submit
                    document.body.appendChild(form);
                    console.log('Form added to document, submitting...');
                    form.submit();
                }, 100);
            } else {
                console.log('User cancelled deletion');
            }
        }
        
        // Use a more explicit confirmation dialog
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this service?\n\nThis action cannot be undone and will remove all service data including:\n- Service information\n- Associated images\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.');
        
        // Handle both Promise and boolean returns
        if (confirmDelete && typeof confirmDelete.then === 'function') {
            // If it's a Promise, wait for it to resolve
            confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
        } else {
            // If it's a boolean, handle it directly
            handleConfirmation(confirmDelete);
        }
        
        return false;
    };
});
</script>
@endpush
