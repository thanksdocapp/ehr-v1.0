@extends('admin.layouts.app')

@section('title', 'Edit FAQ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('faqs.index') }}">FAQs</a></li>
    <li class="breadcrumb-item active">Edit FAQ</li>
@endsection

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.form-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
}

.form-section-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.active {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit FAQ</h1>
        <p class="page-subtitle text-muted">Update frequently asked question and answer</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editFAQForm" action="{{ contextRoute('faqs.update', $faq) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- FAQ Content Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>FAQ Content</h4>
                        <small class="opacity-75">Update question and answer details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="question" class="form-label">
                                <i class="fas fa-question-circle me-1"></i>Question *
                            </label>
                            <textarea class="form-control @error('question') is-invalid @enderror" 
                                      id="question" name="question" rows="3" 
                                      placeholder="Enter FAQ question" 
                                      maxlength="500" required>{{ old('question', $faq->question) }}</textarea>
                            <div class="form-help">Clear and concise question (maximum 500 characters)</div>
                            @error('question')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="answer" class="form-label">
                                <i class="fas fa-comment-dots me-1"></i>Answer *
                            </label>
                            <textarea class="form-control @error('answer') is-invalid @enderror" 
                                      id="answer" name="answer" rows="8" 
                                      placeholder="Enter FAQ answer" required>{{ old('answer', $faq->answer) }}</textarea>
                            <div class="form-help">Detailed answer with HTML formatting support</div>
                            @error('answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- FAQ Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>FAQ Settings</h4>
                        <small class="opacity-75">Category and display settings</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">
                                        <i class="fas fa-tags me-1"></i>Category *
                                    </label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $key => $category)
                                            <option value="{{ $key }}" {{ old('category', $faq->category) == $key ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-help">Group related FAQs together</div>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order" class="form-label">
                                        <i class="fas fa-sort me-1"></i>Sort Order
                                    </label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $faq->sort_order) }}" 
                                           min="0" placeholder="0">
                                    <div class="form-help">Lower numbers appear first</div>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $faq->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on me-1"></i>Active FAQ
                                </label>
                            </div>
                            <div class="form-help">Enable this FAQ to display on the website</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update FAQ
                        </button>
                        <a href="{{ contextRoute('faqs.show', $faq) }}" class="btn btn-info btn-lg me-3">
                            <i class="fas fa-eye me-2"></i>View FAQ
                        </a>
                        <a href="{{ contextRoute('faqs.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>FAQ Status</h6>
                <div class="mb-3">
                    <strong>Status:</strong> 
                    <span class="status-badge {{ $faq->is_active ? 'active' : 'inactive' }}">
                        {{ $faq->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Category:</strong> {{ $categories[$faq->category] ?? 'Unknown' }}
                </div>
                <div class="mb-3">
                    <strong>Sort Order:</strong> {{ $faq->sort_order }}
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ formatDate($faq->created_at) }}
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong> {{ formatDate($faq->updated_at) }}
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Update Guidelines</h6>
                <ul>
                    <li>Keep questions clear and specific</li>
                    <li>Ensure answers are comprehensive</li>
                    <li>Use appropriate HTML formatting</li>
                    <li>Update category if content changes</li>
                    <li>Test all links and references</li>
                    <li>Review for accuracy after updates</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-list me-2"></i>Available Categories</h6>
                <ul>
                    @foreach($categories as $key => $category)
                        <li>{{ $category }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('faqs.show', $faq) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View This FAQ
                    </a>
                    <a href="{{ contextRoute('faqs.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All FAQs
                    </a>
                    <a href="{{ contextRoute('faqs.create') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Add New FAQ
                    </a>
                    <a href="{{ contextRoute('faqs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to FAQs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Rich text editor for answer
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#answer',
            height: 300,
            plugins: 'advlist autolink lists link image charmap print preview anchor',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });
    }
    
    // Form validation
    $('#editFAQForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $('.form-control[required], .form-select[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Check TinyMCE content
        if (typeof tinymce !== 'undefined') {
            const content = tinymce.get('answer').getContent();
            if (content.trim() === '') {
                $('#answer').addClass('is-invalid');
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Character counter for question
    $('#question').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        if (!$(this).next('.char-counter').length) {
            $(this).after('<small class="text-muted char-counter"></small>');
        }
        
        const counter = $(this).next('.char-counter');
        counter.text(remaining + ' characters remaining');
        
        if (remaining < 50) {
            counter.addClass('text-warning');
        } else {
            counter.removeClass('text-warning');
        }
        
        if (remaining < 0) {
            counter.addClass('text-danger');
            $(this).addClass('is-invalid');
        } else {
            counter.removeClass('text-danger');
            $(this).removeClass('is-invalid');
        }
    });

    // Remove validation errors on input
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Initialize character counter
    $('#question').trigger('input');
});
</script>
@endpush
