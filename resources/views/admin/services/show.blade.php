@extends('admin.layouts.app')

@section('title', 'Service Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Service Details</h3>
                    <div>
                        <a href="{{ contextRoute('services.edit', $service) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h4 class="text-primary">{{ $service->name }}</h4>
                                @if($service->image)
                                    <img src="{{ asset('storage/' . $service->image) }}" 
                                         alt="{{ $service->name }}" 
                                         class="img-fluid rounded mb-3" 
                                         style="max-height: 300px;">
                                @endif
                            </div>

                            <div class="mb-4">
                                <h5 class="text-primary">Description</h5>
                                <div class="border rounded p-3 bg-light">
                                    {!! $service->description !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-primary">Price</h6>
                                        @if($service->price)
                                            <span class="fs-5 fw-bold text-success">${{ number_format($service->price, 2) }}</span>
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-primary">Duration</h6>
                                        @if($service->duration)
                                            <span class="fs-6 fw-bold">{{ $service->duration }} minutes</span>
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Service Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Department:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge bg-primary">{{ $service->department->name }}</span>
                                        </dd>

                                        <dt class="col-sm-5">Status:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Price:</dt>
                                        <dd class="col-sm-7">
                                            @if($service->price)
                                                ${{ number_format($service->price, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Duration:</dt>
                                        <dd class="col-sm-7">
                                            @if($service->duration)
                                                {{ $service->duration }} min
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Created:</dt>
                                        <dd class="col-sm-7">{{ formatDateTime($service->created_at) }}</dd>

                                        <dt class="col-sm-5">Updated:</dt>
                                        <dd class="col-sm-7">{{ formatDateTime($service->updated_at) }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Department Details</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Name:</dt>
                                        <dd class="col-sm-7">{{ $service->department->name }}</dd>

                                        <dt class="col-sm-5">Head:</dt>
                                        <dd class="col-sm-7">
                                            @if($service->department->head_of_department)
                                                {{ $service->department->head_of_department }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Location:</dt>
                                        <dd class="col-sm-7">
                                            @if($service->department->location)
                                                {{ $service->department->location }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Phone:</dt>
                                        <dd class="col-sm-7">
                                            @if($service->department->phone)
                                                {{ $service->department->phone }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-{{ $service->is_active ? 'warning' : 'success' }} btn-sm toggle-status" 
                                                data-url="{{ contextRoute('services.toggle-status', $service) }}">
                                            <i class="fas fa-toggle-{{ $service->is_active ? 'on' : 'off' }}"></i>
                                            {{ $service->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        
                                        <a href="{{ contextRoute('services.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Add New Service
                                        </a>
                                        
                                        <a href="{{ contextRoute('departments.show', $service->department) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-building"></i> View Department
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ contextRoute('services.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Services
                        </a>
                        <div>
                            <a href="{{ contextRoute('services.edit', $service) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Service
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteService({{ $service->id }})">
                                <i class="fas fa-trash"></i> Delete Service
                            </button>
                        </div>
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
                    location.reload(); // Reload to show updated status
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
