@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Send Email to Patient')
@section('page-title', 'Send Email to Patient')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-envelope me-2 text-primary"></i>Send Email to Patient
                    </h1>
                    <p class="text-muted mb-0">Compose and send an email to a patient</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.patient-email.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-inbox me-2"></i>View Sent Emails
                    </a>
                    <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Patients
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error:</strong> Please correct the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.patient-email.send') }}" method="POST" id="emailForm" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-edit me-2"></i>Email Details
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <!-- Patient Selector -->
                        <div class="mb-4">
                            <label for="patient_id" class="form-label">
                                Patient <span class="text-danger">*</span>
                            </label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       id="patientSearchInput"
                                       placeholder="Search patient by name, phone, or email…"
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="patientSearchClearBtn" style="display:none;">
                                    Clear
                                </button>
                            </div>
                            <small class="text-muted d-block mb-2" id="patientSearchMeta" style="display:none;"></small>
                            <select class="form-control @error('patient_id') is-invalid @enderror" 
                                    id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                            {{ old('patient_id', $selectedPatientId ?? null) == $patient->id ? 'selected' : '' }}
                                            {{ empty($patient->email) ? 'data-no-email="true"' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }}
                                        @if($patient->phone)
                                            - {{ $patient->phone }}
                                        @endif
                                        @if(empty($patient->email))
                                            <span class="text-warning">(No email)</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Only patients with email addresses can receive emails.
                            </small>
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <label for="subject" class="form-label">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   value="{{ old('subject') }}"
                                   placeholder="Enter email subject..."
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message Body -->
                        <div class="mb-3">
                            <label for="body" class="form-label">
                                Message <span class="text-danger">*</span>
                            </label>
                            <!-- Quill Rich Text Editor Container -->
                            <div id="quill-email-editor" style="min-height: 350px;"></div>
                            <!-- Hidden textarea for form submission -->
                            <textarea class="form-control @error('body') is-invalid @enderror d-none" 
                                      id="body" 
                                      name="body"
                                      required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                The email will automatically include your name, specialisation, clinic name, and date sent in the footer.
                            </small>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-3">
                            <label for="attachments" class="form-label">
                                <i class="fas fa-paperclip me-1"></i>Attachments
                            </label>
                            <input type="file" 
                                   name="attachments[]" 
                                   id="attachments" 
                                   class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt,.rtf">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                You can attach multiple files (PDF, DOC, DOCX, images, TXT, RTF). Maximum file size: 10MB per file.
                            </small>
                            <div id="attachment-list" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary" id="sendBtn">
                        <i class="fas fa-paper-plane me-2"></i>Send Email
                    </button>
                    <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Email Information
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <strong>From:</strong>
                            <div class="mt-1">
                                <div>{{ $doctor->name ?? auth()->user()->name }}</div>
                                @if($doctor->specialization)
                                    <small class="text-muted">{{ $doctor->specialization }}</small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Automatic Footer:</strong>
                            <div class="mt-1 small text-muted">
                                Your email will automatically include:
                                <ul class="mb-0 mt-2">
                                    <li>Your full name</li>
                                    <li>Your role/specialisation</li>
                                    <li>Clinic name</li>
                                    <li>Date sent</li>
                                    <li>Standard disclaimer</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Note:</strong> Replies are disabled by default for patient safety and compliance.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    /* Ensure Quill color picker is visible and properly styled */
    .ql-toolbar .ql-color .ql-picker-label,
    .ql-toolbar .ql-background .ql-picker-label {
        width: 28px;
        height: 24px;
    }
    
    .ql-toolbar .ql-color .ql-picker-options,
    .ql-toolbar .ql-background .ql-picker-options {
        min-width: 152px;
        z-index: 1050;
    }
    
    .ql-toolbar .ql-color .ql-picker-item,
    .ql-toolbar .ql-background .ql-picker-item {
        width: 16px;
        height: 16px;
        border: 1px solid transparent;
        margin: 2px;
    }
    
    /* Ensure Quill editor inline color styles are preserved and displayed */
    /* Inline styles should work by default, but we ensure no parent rules interfere */
    #quill-email-editor .ql-editor [style*="color"] {
        /* Inline styles have highest CSS specificity - no override needed */
        /* This rule ensures parent color rules don't interfere */
    }
</style>
@endpush

@push('scripts')
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<!-- Shared Quill Initialization -->
<script src="{{ asset('js/quill-init.js') }}" onerror="console.warn('quill-init.js failed to load, using fallback initialization')"></script>
<script>
$(document).ready(function() {
    let quillEmail;

    // Fallback initialization if quill-init.js didn't load (complete implementation)
    if (typeof window.initQuillEditor === 'undefined') {
        const defaultQuillConfig = {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            },
            formats: ['bold', 'italic', 'underline', 'header', 'list', 'link'],
            placeholder: 'Start typing...'
        };
        
        window.initQuillEditor = function(selector, options) {
            if (typeof Quill === 'undefined') {
                console.error('Quill is not loaded. Please include quill.min.js before this script.');
                return null;
            }
            const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
            if (!element) {
                console.error('Quill editor container not found: ' + selector);
                return null;
            }
            const config = Object.assign({}, defaultQuillConfig, options || {});
            try {
                return new Quill(element, config);
            } catch (error) {
                console.error('Failed to initialize Quill editor:', error);
                return null;
            }
        };
        
        window.setQuillContent = function(quill, html) {
            if (!quill || !quill.root) {
                console.error('Invalid Quill instance');
                return;
            }
            if (html) {
                quill.root.innerHTML = html;
            } else {
                quill.setText('');
            }
        };
        
        window.syncQuillToTextarea = function(quill, textareaSelector, debounceMs) {
            if (!quill || !quill.root) {
                console.error('Invalid Quill instance');
                return;
            }
            debounceMs = debounceMs || 300;
            const textarea = typeof textareaSelector === 'string' ? document.querySelector(textareaSelector) : textareaSelector;
            if (!textarea) {
                console.error('Textarea not found: ' + textareaSelector);
                return;
            }
            textarea.value = quill.root.innerHTML;
            let debounceTimer;
            const updateTextarea = function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    textarea.value = quill.root.innerHTML;
                }, debounceMs);
            };
            quill.on('text-change', updateTextarea);
            quill.on('selection-change', updateTextarea);
        };
        
        window.getQuillContent = function(quill) {
            if (!quill || !quill.root) return '';
            return quill.root.innerHTML;
        };
        
        window.getQuillText = function(quill) {
            if (!quill) return '';
            return quill.getText();
        };
    }

    // Initialize Quill for rich text email body with full formatting options
    quillEmail = window.initQuillEditor('#quill-email-editor', {
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['link'],
                ['clean']
            ]
        },
        formats: ['bold', 'italic', 'underline', 'strike', 'header', 'size', 'color', 'background', 'align', 'list', 'indent', 'link'],
        placeholder: 'Enter your message to the patient...'
    });

    // Load existing content if any
    const textarea = document.getElementById('body');
    if (textarea && textarea.value) {
        window.setQuillContent(quillEmail, textarea.value);
    }

    // Sync Quill content to hidden textarea
    window.syncQuillToTextarea(quillEmail, '#body');

    // Handle file attachment display
    (function initAttachmentDisplay() {
        const attachmentInput = document.getElementById('attachments');
        const attachmentList = document.getElementById('attachment-list');
        
        if (attachmentInput && attachmentList) {
            attachmentInput.addEventListener('change', function(e) {
                attachmentList.innerHTML = '';
                const files = Array.from(e.target.files);
                
                if (files.length > 0) {
                    const listHtml = files.map(file => {
                        const sizeKB = (file.size / 1024).toFixed(2);
                        return `
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-light rounded">
                                <div>
                                    <i class="fas fa-file me-2"></i>
                                    <span>${file.name}</span>
                                    <small class="text-muted ms-2">(${sizeKB} KB)</small>
                                </div>
                            </div>
                        `;
                    }).join('');
                    attachmentList.innerHTML = listHtml;
                } else {
                    attachmentList.innerHTML = '';
                }
            });
        }
    })();

    // Patient search functionality (client-side filtering)
    (function initPatientSelectSearch() {
        const select = document.getElementById('patient_id');
        const input = document.getElementById('patientSearchInput');
        const clearBtn = document.getElementById('patientSearchClearBtn');
        const meta = document.getElementById('patientSearchMeta');
        
        if (!select || !input || !clearBtn || !meta) return;

        const cachedOptions = Array.from(select.options).map(opt => ({
            value: opt.value,
            label: (opt.textContent || '').trim(),
            disabled: !!opt.disabled,
            noEmail: opt.dataset.noEmail === 'true',
        }));

        const placeholder = cachedOptions[0] || { value: '', label: 'Select Patient', disabled: false, noEmail: false };
        const patients = cachedOptions.slice(1).filter(o => o.value);
        const totalPatients = patients.length;

        function rebuildOptions(optionsToShow, currentValue) {
            select.innerHTML = '';

            const ph = document.createElement('option');
            ph.value = placeholder.value;
            ph.textContent = placeholder.label;
            ph.disabled = placeholder.disabled;
            select.appendChild(ph);

            const selected = patients.find(p => p.value === currentValue);
            if (selected && !optionsToShow.some(p => p.value === currentValue)) {
                optionsToShow = [selected, ...optionsToShow];
            }

            for (const item of optionsToShow) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                if (item.noEmail) {
                    opt.dataset.noEmail = 'true';
                }
                select.appendChild(opt);
            }

            if (currentValue) {
                select.value = currentValue;
            }
        }

        function updateMeta(query, visibleCount) {
            if (!query) {
                meta.style.display = 'none';
                meta.textContent = '';
                return;
            }
            meta.style.display = 'block';
            meta.textContent = visibleCount > 0
                ? `Found ${visibleCount} of ${totalPatients} patients`
                : 'No patients found. Try a different search.';
        }

        let t = null;
        function applyFilter() {
            const query = (input.value || '').toLowerCase().trim();
            clearBtn.style.display = query ? 'inline-block' : 'none';

            const currentValue = select.value;
            if (!query) {
                rebuildOptions(patients, currentValue);
                updateMeta('', 0);
                return;
            }

            const matches = patients.filter(p => (p.label || '').toLowerCase().includes(query));
            rebuildOptions(matches, currentValue);
            updateMeta(query, matches.length);
        }

        input.addEventListener('input', () => {
            if (t) window.clearTimeout(t);
            t = window.setTimeout(applyFilter, 150);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            applyFilter();
            input.focus();
        });

        applyFilter();
    })();

    // Validate patient has email before submitting
    $('#emailForm').on('submit', function(e) {
        const selectedOption = $('#patient_id option:selected');
        const patientId = $('#patient_id').val();

        if (!patientId) {
            e.preventDefault();
            alert('Please select a patient.');
            return false;
        }

        if (selectedOption.data('no-email') === true) {
            e.preventDefault();
            alert('This patient does not have an email address. Please select a different patient or update the patient\'s email address.');
            return false;
        }

        // Update Quill content to textarea before submit
        if (quillEmail) {
            const html = window.getQuillContent(quillEmail);
            const textarea = document.getElementById('body');
            if (textarea) {
                textarea.value = html;
            }
        }

        // Show loading state
        const sendBtn = $('#sendBtn');
        const originalHtml = sendBtn.html();
        sendBtn.prop('disabled', true);
        sendBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');
    });
});
</script>
@endpush

