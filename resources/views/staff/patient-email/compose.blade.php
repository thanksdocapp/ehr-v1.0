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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

    <form action="{{ route('staff.patient-email.send') }}" method="POST" id="emailForm">
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
                            <textarea class="form-control @error('body') is-invalid @enderror" 
                                      id="body" 
                                      name="body" 
                                      rows="12"
                                      placeholder="Enter your message to the patient..."
                                      required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                The email will automatically include your name, specialization, clinic name, and department in the footer.
                            </small>
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
                                    <li>Your role/specialization</li>
                                    <li>Clinic name</li>
                                    <li>Department (if applicable)</li>
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

@push('scripts')
<script src="{{ getTinyMceCdnUrl() }}" referrerpolicy="origin"></script>
<script>
$(document).ready(function() {
    // Initialize TinyMCE for rich text email body with full formatting options
    tinymce.init({
        selector: '#body',
        height: 450,
        menubar: false,
        plugins: [
            'lists', 'link', 'table', 'code', 'colorpicker', 
            'textcolor', 'charmap', 'hr', 'nonbreaking', 
            'paste', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect fontselect fontsizeselect | ' +
                 'bold italic underline strikethrough | forecolor backcolor | ' +
                 'alignleft aligncenter alignright alignjustify | ' +
                 'bullist numlist | outdent indent | ' +
                 'link table charmap hr nonbreaking | code | removeformat',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
        branding: false,
        promotion: false,
        font_formats: 'Arial=arial,helvetica,sans-serif;' +
                     'Courier New=courier new,courier;' +
                     'Georgia=georgia,palatino;' +
                     'Helvetica=helvetica;' +
                     'Impact=impact,chicago;' +
                     'Times New Roman=times new roman,times;' +
                     'Trebuchet MS=trebuchet ms,geneva;' +
                     'Verdana=verdana,geneva',
        fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt',
        paste_as_text: false,
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: false,
        paste_remove_styles_if_webkit: false,
        paste_strip_class_attributes: 'none',
        paste_merge_formats: true,
        invalid_elements: 'script,iframe,object,embed',
        extended_valid_elements: 'span[*],div[*],table[*],thead[*],tbody[*],tr[*],td[*],th[*]',
    });

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

        // Update TinyMCE content to textarea before submit
        if (typeof tinymce !== 'undefined' && tinymce.get('body')) {
            tinymce.get('body').save();
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

