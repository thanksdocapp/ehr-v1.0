@extends('admin.layouts.app')

@section('title', 'FAQ Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">FAQ Details</h3>
                    <div>
                        <a href="{{ contextRoute('faqs.edit', $faq) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ contextRoute('faqs.destroy', $faq) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm delete-btn">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h5 class="text-primary">Question</h5>
                                <p class="fs-6 fw-bold">{{ $faq->question }}</p>
                            </div>

                            <div class="mb-4">
                                <h5 class="text-primary">Answer</h5>
                                <div class="border rounded p-3 bg-light">
                                    {!! $faq->answer !!}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">FAQ Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Category:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge bg-info">
                                                {{ $faq->category == 'general' ? 'General Questions' : 
                                                   ($faq->category == 'appointments' ? 'Appointments' :
                                                   ($faq->category == 'services' ? 'Medical Services' :
                                                   ($faq->category == 'emergency' ? 'Emergency Care' :
                                                   ($faq->category == 'insurance' ? 'Insurance & Billing' : $faq->category)))) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Sort Order:</dt>
                                        <dd class="col-sm-7">{{ $faq->sort_order }}</dd>

                                        <dt class="col-sm-5">Status:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge {{ $faq->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $faq->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Created:</dt>
                                        <dd class="col-sm-7">{{ formatDateTime($faq->created_at) }}</dd>

                                        <dt class="col-sm-5">Updated:</dt>
                                        <dd class="col-sm-7">{{ formatDateTime($faq->updated_at) }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-{{ $faq->is_active ? 'warning' : 'success' }} btn-sm toggle-status" 
                                                data-url="{{ contextRoute('faqs.toggle-status', $faq) }}">
                                            <i class="fas fa-toggle-{{ $faq->is_active ? 'on' : 'off' }}"></i>
                                            {{ $faq->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        
                                        <a href="{{ contextRoute('faqs.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Add New FAQ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ contextRoute('faqs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to FAQs
                        </a>
                        <a href="{{ contextRoute('faqs.edit', $faq) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit FAQ
                        </a>
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
                toastr.error('Error updating FAQ status');
            }
        });
    });

    // Delete confirmation
    $('.delete-btn').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this FAQ? This action cannot be undone.')) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush
