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
                            <label for="patient_search" class="form-label">Select Patient <span class="text-danger">*</span></label>
                            <input type="hidden" name="patient_id" id="patient_id" value="{{ $patient->id ?? '' }}" required>
                            <input type="text"
                                   class="form-control @error('patient_id') is-invalid @enderror"
                                   id="patient_search"
                                   placeholder="Type to search for a patient..."
                                   value="{{ $patient ? ($patient->full_name ?? $patient->first_name . ' ' . $patient->last_name) . ' (' . ($patient->patient_id ?? 'ID: ' . $patient->id) . ')' : '' }}"
                                   autocomplete="off">
                            <div id="patient_results" class="list-group position-absolute w-100 shadow-sm bg-white" style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                            @error('patient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
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

@push('styles')
<style>
    #patient_results .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
    }
    #patient_results .list-group-item:hover,
    #patient_results .list-group-item.active {
        background-color: #0d6efd;
        color: white;
    }
    #patient_results .list-group-item:first-child {
        border-top: none;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patient_search');
    const patientIdInput = document.getElementById('patient_id');
    const resultsContainer = document.getElementById('patient_results');
    const templateSelect = document.getElementById('template_id');
    const previewBtn = document.getElementById('previewBtn');
    const previewSection = document.getElementById('previewSection');
    const documentPreview = document.getElementById('documentPreview');
    let debounceTimer;

    // Patient search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);
        patientIdInput.value = '';
        checkPreviewReady();

        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(function() {
            fetch('{{ route("admin.patients.search") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    const results = data.results || data;

                    if (results.length === 0) {
                        resultsContainer.innerHTML = '<div class="list-group-item text-muted">No patients found</div>';
                    } else {
                        results.forEach(function(patient) {
                            const item = document.createElement('div');
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = patient.text;
                            item.dataset.id = patient.id;

                            item.addEventListener('click', function() {
                                patientIdInput.value = this.dataset.id;
                                searchInput.value = this.textContent;
                                resultsContainer.style.display = 'none';
                                checkPreviewReady();
                            });

                            resultsContainer.appendChild(item);
                        });
                    }

                    resultsContainer.style.display = 'block';
                })
                .catch(error => {
                    console.error('Search error:', error);
                    resultsContainer.innerHTML = '<div class="list-group-item text-danger">Error searching patients</div>';
                    resultsContainer.style.display = 'block';
                });
        }, 300);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    // Show results when focusing on input
    searchInput.addEventListener('focus', function() {
        if (resultsContainer.children.length > 0 && !patientIdInput.value) {
            resultsContainer.style.display = 'block';
        }
    });

    // Enable preview button when both template and patient are selected
    function checkPreviewReady() {
        const templateId = templateSelect.value;
        const patientId = patientIdInput.value;
        previewBtn.disabled = !(templateId && patientId);
    }

    templateSelect.addEventListener('change', function() {
        checkPreviewReady();
        previewSection.style.display = 'none';
    });

    // Preview functionality
    previewBtn.addEventListener('click', function() {
        const templateId = templateSelect.value;
        const patientId = patientIdInput.value;

        if (!templateId || !patientId) {
            return;
        }

        documentPreview.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading preview...</p></div>';
        previewSection.style.display = 'block';

        fetch('{{ route("admin.generated-documents.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                template_id: templateId,
                patient_id: patientId
            })
        })
        .then(response => response.json())
        .then(data => {
            documentPreview.innerHTML =
                '<div class="bg-white p-3 border">' +
                '<div class="text-center mb-3"><strong>' + data.template_name + '</strong><br><small class="text-muted">For: ' + data.patient_name + '</small></div>' +
                '<hr>' +
                data.content +
                '</div>';
        })
        .catch(error => {
            documentPreview.innerHTML = '<div class="alert alert-danger">Failed to load preview. Please try again.</div>';
        });
    });

    // Initial check
    checkPreviewReady();
});
</script>
@endpush
