@extends('admin.layouts.app')

@section('title', 'FAQ Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">FAQ Management</h3>
                    <a href="{{ contextRoute('faqs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New FAQ
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ contextRoute('faqs.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search FAQs..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $key => $category)
                                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $category }}</option>
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
                                    <a href="{{ contextRoute('faqs.index') }}" class="btn btn-outline-secondary">Clear</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- FAQs Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Category</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($faqs as $faq)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ Str::limit($faq->question, 60) }}</div>
                                            <small class="text-muted">{{ Str::limit($faq->answer, 100) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $categories[$faq->category] ?? $faq->category }}</span>
                                        </td>
                                        <td>{{ $faq->sort_order }}</td>
                                        <td>
                                            <button class="btn btn-sm {{ $faq->is_active ? 'btn-success' : 'btn-secondary' }} toggle-status" 
                                                    data-id="{{ $faq->id }}" 
                                                    data-url="{{ contextRoute('faqs.toggle-status', $faq) }}">
                                                {{ $faq->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td>{{ formatDate($faq->created_at) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ contextRoute('faqs.show', $faq) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ contextRoute('faqs.edit', $faq) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="deleteFaq({{ $faq->id }}); return false;" 
                                                        title="Delete FAQ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No FAQs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $faqs->withQueryString()->links() }}
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
                toastr.error('Error updating FAQ status');
            }
        });
    });
});

// Delete FAQ function with enhanced confirmation
function deleteFaq(faqId) {
    console.log('Delete FAQ called with ID:', faqId);
    
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
                form.action = `/admin/faqs/${faqId}`;
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
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this FAQ?\n\nThis action cannot be undone and will remove:\n- The question and answer\n- All associated metadata\n- Any references to this FAQ\n\nClick OK to confirm deletion or Cancel to abort.');
    
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
