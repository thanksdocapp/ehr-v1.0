@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Template')
@section('page-title', 'Edit Template')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('staff.templates.show', $template) }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Template
            </a>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i>Edit: {{ $template->name }}
            </h1>
        </div>
    </div>

    <form action="{{ route('staff.templates.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">Template Details</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="letter" {{ old('type', $template->type) == 'letter' ? 'selected' : '' }}>Letter</option>
                                <option value="form" {{ old('type', $template->type) == 'form' ? 'selected' : '' }}>Form</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="20">{{ old('content', $template->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-save me-2"></i>Update Template
                    </button>
                    <a href="{{ route('staff.templates.show', $template) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-code me-2"></i>Placeholders
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="text-muted small">Click to insert into content:</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(\App\Models\Template::DEFAULT_PLACEHOLDERS as $placeholder)
                                <button type="button" class="btn btn-sm btn-outline-secondary placeholder-btn"
                                        data-placeholder="{{ $placeholder }}">
                                    {{ $placeholder }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    let editor;
    tinymce.init({
        selector: '#content',
        height: 500,
        plugins: 'lists link table code',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | table | code',
        setup: function(ed) {
            editor = ed;
        }
    });

    document.querySelectorAll('.placeholder-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const placeholder = this.dataset.placeholder;
            if (editor) {
                editor.insertContent(placeholder);
            }
        });
    });
</script>
@endpush
