@extends('admin.layouts.app')

@section('title', 'About Statistics Management')
@section('page-title', 'About Statistics')

@section('content')
<div class="container-fluid">
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">About Statistics Management</h1>
            <p class="text-muted">Manage statistics displayed in the About Us section</p>
        </div>
        <a href="{{ contextRoute('about-stats.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Statistic
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        @forelse($aboutStats as $stat)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <!-- Icon -->
                        <div class="mb-3">
                            @if($stat->icon)
                                <i class="{{ $stat->icon }} fs-2" style="color: {{ $stat->display_color }};"></i>
                            @else
                                <i class="fas fa-chart-bar fs-2" style="color: {{ $stat->display_color }};"></i>
                            @endif
                        </div>
                        
                        <!-- Value -->
                        <h3 class="fw-bold mb-2" style="color: {{ $stat->display_color }};">
                            {{ $stat->formatted_value }}
                        </h3>
                        
                        <!-- Title -->
                        <h5 class="fw-semibold mb-1">{{ $stat->title }}</h5>
                        
                        <!-- Subtitle -->
                        @if($stat->subtitle)
                            <p class="text-muted small mb-2">{{ $stat->subtitle }}</p>
                        @endif

                        <!-- Description -->
                        @if($stat->description)
                            <p class="text-muted small mb-3">{{ Str::limit($stat->description, 80) }}</p>
                        @endif

                        <!-- Status Badge -->
                        <span class="badge {{ $stat->is_active ? 'bg-success' : 'bg-secondary' }} mb-3">
                            {{ $stat->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ contextRoute('about-stats.show', $stat) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ contextRoute('about-stats.edit', $stat) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-{{ $stat->is_active ? 'warning' : 'success' }}" 
                                    onclick="toggleStatus({{ $stat->id }})" 
                                    title="{{ $stat->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-{{ $stat->is_active ? 'pause' : 'play' }}"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteStat({{ $stat->id }}); return false;" 
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-bar text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mb-3">No Statistics Found</h5>
                        <p class="text-muted mb-4">Create your first statistic to get started.</p>
                        <a href="{{ contextRoute('about-stats.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add First Statistic
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>


@endsection

@push('scripts')
<script>
    function toggleStatus(statId) {
        // Show a confirmation dialog
        const confirmChange = confirm('Are you sure you want to change this statistic\'s status?\n\nThis action can be reverted, but it will affect the statistic\'s visibility on the website.');
        
        // Check if confirmation is a Promise
        if (confirmChange && typeof confirmChange.then === 'function') {
            confirmChange.then(result => {
                if (result) sendStatusChangeRequest(statId);
            }).catch(() => {});
        } else if (confirmChange) {
            sendStatusChangeRequest(statId);
        }
    }

    function sendStatusChangeRequest(statId) {
        // Make the request to toggle the status
        fetch(`/admin/about-stats/${statId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error changing statistic status');
            }
        })
        .catch(error => {
            console.error('Error toggling statistic status:', error);
            alert('An error occurred while changing the statistic status. Please try again.');
        });
    }

    function deleteStat(statId) {
        console.log('Delete statistic called with ID:', statId);
        
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
                    form.action = `/admin/about-stats/${statId}`;
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
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this statistic?\n\nThis action cannot be undone and will remove the statistic from your website.\n\nClick OK to confirm deletion or Cancel to abort.');
        
        // Handle both Promise and boolean returns
        if (confirmDelete && typeof confirmDelete.then === 'function') {
            // If it's a Promise, wait for it to resolve
            confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
        } else {
            // If it's a boolean, handle it directly
            handleConfirmation(confirmDelete);
        }
        
        return false;
    }
</script>
@endpush
