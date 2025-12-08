@extends('admin.layouts.app')

@section('title', 'Generate Document')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.generated-documents.index') }}">Generated Documents</a></li>
    <li class="breadcrumb-item active">Generate New</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-pdf me-2 text-primary"></i>Generate Document
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.generated-documents.store') }}" method="POST" id="generateForm">
                        @csrf

                        <!-- Template Selection -->
                        <div class="mb-4">
                            <label for="template_id" class="form-label">Select Template <span class="text-danger">*</span></label>
                            <select class="form-select @error('template_id') is-invalid @enderror"
                                    id="template_id" name="template_id" required>
                                <option value="">Choose a template...</option>
                                @php
                                    $letterTemplates = $templates->where('type', 'letter');
                                    $formTemplates = $templates->where('type', 'form');
                                @endphp
                                @if($letterTemplates->count() > 0)
                                    <optgroup label="Letters">
                                        @foreach($letterTemplates as $tpl)
                                            <option value="{{ $tpl->id }}"
                                                    {{ old('template_id', $template->id ?? '') == $tpl->id ? 'selected' : '' }}>
                                                {{ $tpl->name }}
                                                @if($tpl->is_system) (System) @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if($formTemplates->count() > 0)
                                    <optgroup label="Forms">
                                        @foreach($formTemplates as $tpl)
                                            <option value="{{ $tpl->id }}"
                                                    {{ old('template_id', $template->id ?? '') == $tpl->id ? 'selected' : '' }}>
                                                {{ $tpl->name }}
                                                @if($tpl->is_system) (System) @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Patient Selection -->
                        <div class="mb-4">
                            <label for="patient_id" class="form-label">Select Patient <span class="text-danger">*</span></label>
                            <select class="form-select @error('patient_id') is-invalid @enderror"
                                    id="patient_id" name="patient_id" required>
                                <option value="">Search for a patient...</option>
                                @if($patient)
                                    <option value="{{ $patient->id }}" selected>
                                        {{ $patient->full_name ?? $patient->first_name . ' ' . $patient->last_name }}
                                        ({{ $patient->patient_id ?? 'ID: ' . $patient->id }})
                                    </option>
                                @endif
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Start typing to search for patients</small>
                        </div>

                        <!-- Document Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Document Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="Leave blank to use template name">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Internal Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="2"
                                      placeholder="Optional notes (not included in document)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Preview Section -->
                        <div id="previewSection" class="mb-4" style="display: none;">
                            <label class="form-label">Document Preview</label>
                            <div class="border rounded p-4 bg-light" id="documentPreview">
                                <p class="text-muted text-center mb-0">Select a template and patient to preview</p>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf me-2"></i>Generate Document
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="previewBtn" disabled>
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                            <a href="{{ route('admin.generated-documents.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>How it Works
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Select a template (letter or form)</li>
                        <li class="mb-2">Choose the patient</li>
                        <li class="mb-2">Patient data is automatically merged into placeholders</li>
                        <li class="mb-2">PDF is generated and saved</li>
                        <li>Download, email, or print the document</li>
                    </ol>
                </div>
            </div>

            <!-- Available Placeholders Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-code me-2 text-primary"></i>Data Fields
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">The following patient data will be merged:</p>
                    <ul class="small mb-0 ps-3">
                        <li>Patient name, DOB, age</li>
                        <li>Contact information</li>
                        <li>Address details</li>
                        <li>Doctor/provider information</li>
                        <li>Clinic details</li>
                        <li>Current date/time</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {
    // Initialize Select2 for patient search
    $('#patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search for a patient...',
        allowClear: true,
        ajax: {
            url: '{{ route("admin.patients.search") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.data.map(function(patient) {
                        return {
                            id: patient.id,
                            text: (patient.full_name || patient.first_name + ' ' + patient.last_name) +
                                  ' (' + (patient.patient_id || 'ID: ' + patient.id) + ')'
                        };
                    }),
                    pagination: {
                        more: data.current_page < data.last_page
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });

    // Enable preview button when both template and patient are selected
    function checkPreviewReady() {
        const templateId = $('#template_id').val();
        const patientId = $('#patient_id').val();
        $('#previewBtn').prop('disabled', !(templateId && patientId));
    }

    $('#template_id, #patient_id').on('change', function() {
        checkPreviewReady();
        $('#previewSection').hide();
    });

    // Preview functionality
    $('#previewBtn').on('click', function() {
        const templateId = $('#template_id').val();
        const patientId = $('#patient_id').val();

        if (!templateId || !patientId) {
            return;
        }

        $('#documentPreview').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading preview...</p></div>');
        $('#previewSection').show();

        $.ajax({
            url: '{{ route("admin.generated-documents.preview") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                template_id: templateId,
                patient_id: patientId
            },
            success: function(response) {
                $('#documentPreview').html(
                    '<div class="bg-white p-3 border">' +
                    '<div class="text-center mb-3"><strong>' + response.template_name + '</strong><br><small class="text-muted">For: ' + response.patient_name + '</small></div>' +
                    '<hr>' +
                    response.content +
                    '</div>'
                );
            },
            error: function(xhr) {
                $('#documentPreview').html('<div class="alert alert-danger">Failed to load preview. Please try again.</div>');
            }
        });
    });

    // Initial check
    checkPreviewReady();
});
</script>
@endpush
