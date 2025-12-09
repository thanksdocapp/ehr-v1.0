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
                            <label for="patient_search" class="form-label">Select Patient <span class="text-danger">*</span></label>
                            <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatient->id ?? '' }}" required>
                            <input type="text"
                                   class="form-control @error('patient_id') is-invalid @enderror"
                                   id="patient_search"
                                   placeholder="Type to search for a patient..."
                                   value="{{ $selectedPatient ? $selectedPatient->full_name . ' (' . ($selectedPatient->date_of_birth?->format('d/m/Y') ?? 'No DOB') . ')' : '' }}"
                                   autocomplete="off">
                            <div id="patient_results" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                            @error('patient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
    .patient-search-wrapper {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patient_search');
    const patientIdInput = document.getElementById('patient_id');
    const resultsContainer = document.getElementById('patient_results');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        // Clear previous timer
        clearTimeout(debounceTimer);

        // Clear patient ID when user types (they need to select again)
        patientIdInput.value = '';

        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        // Debounce the search
        debounceTimer = setTimeout(function() {
            fetch('{{ route("staff.patients.search") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';

                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<div class="list-group-item text-muted">No patients found</div>';
                    } else {
                        data.forEach(function(patient) {
                            const item = document.createElement('div');
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = patient.text;
                            item.dataset.id = patient.id;
                            item.dataset.email = patient.email || '';

                            item.addEventListener('click', function() {
                                patientIdInput.value = this.dataset.id;
                                searchInput.value = this.textContent;
                                resultsContainer.style.display = 'none';
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

    // Show results when focusing on input (if there are results)
    searchInput.addEventListener('focus', function() {
        if (resultsContainer.children.length > 0 && !patientIdInput.value) {
            resultsContainer.style.display = 'block';
        }
    });
});
</script>
@endpush
