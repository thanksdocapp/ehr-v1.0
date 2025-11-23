@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Testimonials')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Testimonials</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="page-title mb-1">Testimonials</h4>
                <p class="text-muted mb-0">Manage customer testimonials and reviews</p>
            </div>
            <a href="{{ contextRoute('testimonials.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Testimonial
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Photo</th>
                                <th width="20%">Customer</th>
                                <th width="15%">Company</th>
                                <th width="35%">Review</th>
                                <th width="10%">Rating</th>
                                <th width="10%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($testimonials as $testimonial)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            @if($testimonial->customer_photo)
                                                <img src="{{ Storage::disk('public')->url($testimonial->customer_photo) }}" 
                                                     alt="{{ $testimonial->customer_name }}" 
                                                     class="rounded-circle" 
                                                     width="40" height="40"
                                                     style="object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($testimonial->customer_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $testimonial->customer_name }}</div>
                                        @if($testimonial->customer_position)
                                            <div class="text-muted small">{{ $testimonial->customer_position }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($testimonial->customer_company)
                                            <span class="text-primary">{{ $testimonial->customer_company }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="mb-0">{{ Str::limit($testimonial->review_text, 100) }}</p>
                                        <small class="text-muted">{{ formatDate($testimonial->created_at) }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $testimonial->rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2 small text-muted">({{ $testimonial->rating }})</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" 
                                                   type="checkbox" 
                                                   data-id="{{ $testimonial->id }}"
                                                   data-url="{{ contextRoute('testimonials.toggle-status', $testimonial) }}"
                                                   {{ $testimonial->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label small">
                                                {{ $testimonial->is_active ? 'Active' : 'Inactive' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ contextRoute('testimonials.show', $testimonial) }}" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ contextRoute('testimonials.edit', $testimonial) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ contextRoute('testimonials.destroy', $testimonial) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                                        title="Delete"
                                                        data-confirm="Are you sure you want to delete this testimonial?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-comment-alt fa-2x mb-3"></i>
                                            <p class="mb-0">No testimonials found.</p>
                                            <p class="mb-0">
                                                <a href="{{ contextRoute('testimonials.create') }}" class="text-primary">
                                                    Add your first testimonial
                                                </a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($testimonials->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $testimonials->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Status toggle
    $('.status-toggle').change(function() {
        const toggle = $(this);
        const url = toggle.data('url');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const label = toggle.siblings('label');
                    label.text(response.is_active ? 'Active' : 'Inactive');
                    
                    // Show success notification
                    showNotification(response.message, 'success');
                }
            },
            error: function() {
                toggle.prop('checked', !toggle.prop('checked'));
                showNotification('Failed to update testimonial status.', 'error');
            }
        });
    });

    // Delete confirmation
    $('.delete-btn').click(function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const confirmText = $(this).data('confirm');
        
        confirmAction(confirmText).then(confirmed => {
            if (confirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
