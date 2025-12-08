@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Generate Document')
@section('page-title', 'Generate Document')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('staff.generated-documents.index') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Documents
            </a>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">
                <i class="fas fa-file-pdf me-2 text-danger"></i>Generate Document
            </h1>
            <p class="text-muted">Select a template and patient to generate a PDF document</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.generated-documents.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">Document Details</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-4">
                            <label for="template_id" class="form-label">Select Template <span class="text-danger">*</span></label>
                            <select class="form-control @error('template_id') is-invalid @enderror"
                                    id="template_id" name="template_id" required>
                                <option value="">-- Select a Template --</option>
                                @foreach($templates->groupBy('type') as $type => $typeTemplates)
                                    <optgroup label="{{ ucfirst($type) }}s">
                                        @foreach($typeTemplates as $template)
                                            <option value="{{ $template->id }}"
                                                {{ (old('template_id') == $template->id || ($selectedTemplate && $selectedTemplate->id == $template->id)) ? 'selected' : '' }}>
                                                {{ $template->name }}
                                                @if($template->is_system) (System) @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="patient_id" class="form-label">Select Patient <span class="text-danger">*</span></label>
                            <select class="form-control @error('patient_id') is-invalid @enderror"
                                    id="patient_id" name="patient_id" required>
                                @if($selectedPatient)
                                    <option value="{{ $selectedPatient->id }}" selected>
                                        {{ $selectedPatient->full_name }} ({{ $selectedPatient->date_of_birth?->format('d/m/Y') ?? 'No DOB' }})
                                    </option>
                                @endif
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Start typing to search for a patient</small>
                        </div>

                        <div class="mb-4">
                            <label for="title" class="form-label">Document Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="Leave blank to auto-generate">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional - will be generated from template and patient name if left blank</small>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Internal Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Any internal notes about this document">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-file-pdf me-2"></i>Generate Document
                    </button>
                    <a href="{{ route('staff.generated-documents.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>How It Works
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <ol class="mb-0">
                            <li class="mb-2">Select a template from the list</li>
                            <li class="mb-2">Search and select a patient</li>
                            <li class="mb-2">Click "Generate Document"</li>
                            <li class="mb-2">The document will be created as a draft PDF</li>
                            <li class="mb-2">Review, finalize, and send via email</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search for a patient...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("staff.patients.search") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data };
            },
            cache: true
        }
    });
});
</script>
@endpush
