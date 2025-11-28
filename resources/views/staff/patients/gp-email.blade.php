@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@include('admin.shared.modern-ui')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="modern-page-header mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="modern-page-title">
                    <i class="fas fa-envelope me-2"></i>Contact Patient's GP
                </h1>
                <p class="modern-page-subtitle mb-0">Send an email to {{ $patient->gp_name ?? 'GP' }}</p>
            </div>
            <a href="{{ route('staff.patients.show', $patient) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Patient
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- GP Email Form Card -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Compose Email
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <form action="{{ route('staff.patients.gp-email.send', $patient) }}" method="POST" id="gpEmailForm">
                        @csrf

                        <!-- GP Information Display -->
                        <div class="mb-4 p-3" style="background-color: #f8f9fc; border-radius: 8px; border-left: 4px solid #1a202c;">
                            <h6 class="mb-3" style="color: #1a202c; font-weight: 600;">
                                <i class="fas fa-user-md me-2"></i>GP Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Name:</strong>
                                    <span style="color: #2d3748;">{{ $patient->gp_name ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Email:</strong>
                                    <span style="color: #2d3748;">{{ $patient->gp_email ?? 'N/A' }}</span>
                                </div>
                                @if($patient->gp_phone)
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Phone:</strong>
                                    <span style="color: #2d3748;">{{ $patient->gp_phone }}</span>
                                </div>
                                @endif
                                @if($patient->gp_address)
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Address:</strong>
                                    <span style="color: #2d3748;">{{ $patient->gp_address }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Patient Information Display -->
                        <div class="mb-4 p-3" style="background-color: #f0f9ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <h6 class="mb-3" style="color: #1a202c; font-weight: 600;">
                                <i class="fas fa-user me-2"></i>Patient Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Name:</strong>
                                    <span style="color: #2d3748;">{{ $patient->full_name }}</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Patient ID:</strong>
                                    <span style="color: #2d3748;">{{ $patient->patient_id }}</span>
                                </div>
                                @if($patient->date_of_birth)
                                <div class="col-md-6 mb-2">
                                    <strong style="color: #4a5568;">Date of Birth:</strong>
                                    <span style="color: #2d3748;">{{ $patient->date_of_birth->format('F d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Email Type -->
                        <div class="mb-3">
                            <label for="email_type" class="form-label" style="color: #2d3748; font-weight: 500;">
                                <i class="fas fa-tag me-2"></i>Email Type
                            </label>
                            <select class="form-control @error('email_type') is-invalid @enderror" 
                                    id="email_type" name="email_type" style="border: 2px solid #e2e8f0; border-radius: 6px;">
                                <option value="general" {{ old('email_type') == 'general' ? 'selected' : '' }}>General Communication</option>
                                <option value="consultation" {{ old('email_type') == 'consultation' ? 'selected' : '' }}>Consultation Update</option>
                                <option value="referral" {{ old('email_type') == 'referral' ? 'selected' : '' }}>Referral</option>
                                <option value="update" {{ old('email_type') == 'update' ? 'selected' : '' }}>Patient Update</option>
                                <option value="other" {{ old('email_type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('email_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label" style="color: #2d3748; font-weight: 500;">
                                <i class="fas fa-heading me-2"></i>Subject <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   value="{{ old('subject') }}" 
                                   required
                                   placeholder="Enter email subject"
                                   style="border: 2px solid #e2e8f0; border-radius: 6px;">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label for="message" class="form-label" style="color: #2d3748; font-weight: 500;">
                                <i class="fas fa-comment-alt me-2"></i>Message <span class="text-danger">*</span>
                            </label>
                            <!-- Rich Text Editor Container -->
                            <div id="messageEditor" style="border: 2px solid #e2e8f0; border-radius: 6px; min-height: 300px; background: white; position: relative;"></div>
                            <!-- Hidden textarea for form submission -->
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      required
                                      style="display: none;">{{ old('message') }}</textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Patient information will be automatically included in the email.
                            </small>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('staff.patients.show', $patient) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="sendEmailBtn">
                                <i class="fas fa-paper-plane me-2"></i>Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Guidelines Card -->
            <div class="doctor-card mb-3">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Guidelines
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <ul class="list-unstyled mb-0" style="color: #4a5568; font-size: 14px; line-height: 1.8;">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Ensure patient has consented to share information with GP
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Use clear and professional language
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Include relevant patient information
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            All emails are logged for record keeping
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Tips Card -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Quick Tips
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div style="color: #4a5568; font-size: 14px; line-height: 1.8;">
                        <p class="mb-2">
                            <strong>Subject Line:</strong> Be specific and clear about the purpose of the email.
                        </p>
                        <p class="mb-2">
                            <strong>Message:</strong> Keep it concise but include all necessary information.
                        </p>
                        <p class="mb-0">
                            <strong>Email Type:</strong> Select the appropriate category for better organization.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #messageEditor {
        min-height: 300px;
        background: white;
    }
    #messageEditor .ql-editor {
        min-height: 280px;
        font-size: 14px;
    }
    #messageEditor .ql-container {
        font-family: inherit;
        font-size: 14px;
    }
    #messageEditor .ql-toolbar {
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        border-bottom: 1px solid #e2e8f0;
    }
    #messageEditor .ql-container {
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
    }
</style>
@endpush

@push('scripts')
<!-- Quill Rich Text Editor - Try multiple CDNs for reliability -->
<script>
(function() {
    let quillEditor = null;
    let quillLoaded = false;
    let initAttempts = 0;
    const maxAttempts = 100; // Try for 10 seconds
    
    // Try to load Quill from multiple CDNs
    function loadQuill() {
        if (typeof Quill !== 'undefined') {
            quillLoaded = true;
            initQuillEditor();
            return;
        }
        
        // Try primary CDN
        const script1 = document.createElement('script');
        script1.src = 'https://cdn.quilljs.com/1.3.6/quill.js';
        script1.onload = function() {
            quillLoaded = true;
            initQuillEditor();
        };
        script1.onerror = function() {
            // Try backup CDN
            const script2 = document.createElement('script');
            script2.src = 'https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.js';
            script2.onload = function() {
                quillLoaded = true;
                initQuillEditor();
            };
            script2.onerror = function() {
                console.error('Failed to load Quill from all CDNs');
                showFallbackTextarea();
            };
            document.head.appendChild(script2);
        };
        document.head.appendChild(script1);
    }
    
    function showFallbackTextarea() {
        const editorElement = document.getElementById('messageEditor');
        const messageTextarea = document.getElementById('message');
        
        if (editorElement && messageTextarea) {
            editorElement.style.display = 'none';
            messageTextarea.style.display = 'block';
            messageTextarea.rows = 10;
            messageTextarea.classList.add('form-control');
        }
    }
    
    function initQuillEditor() {
        initAttempts++;
        
        // Check if Quill is loaded
        if (typeof Quill === 'undefined') {
            if (initAttempts < maxAttempts && !quillLoaded) {
                setTimeout(initQuillEditor, 100);
            } else {
                console.error('Quill library failed to load');
                showFallbackTextarea();
            }
            return;
        }
        
        const editorElement = document.getElementById('messageEditor');
        const messageTextarea = document.getElementById('message');
        
        if (!editorElement) {
            console.error('Editor element not found');
            showFallbackTextarea();
            return;
        }
        
        // If already initialized, don't reinitialize
        if (editorElement.querySelector('.ql-container')) {
            console.log('Quill editor already initialized');
            return;
        }
        
        try {
            // Clear any existing content
            editorElement.innerHTML = '';
            
            // Initialize Quill editor
            quillEditor = new Quill('#messageEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'Enter your message to the GP...'
            });
            
            // Set initial content if exists
            if (messageTextarea && messageTextarea.value) {
                const initialContent = messageTextarea.value.trim();
                if (initialContent) {
                    quillEditor.root.innerHTML = initialContent;
                }
            }
            
            // Update hidden textarea on text change
            quillEditor.on('text-change', function() {
                if (messageTextarea) {
                    const html = quillEditor.root.innerHTML;
                    const text = quillEditor.getText().trim();
                    messageTextarea.value = html;
                    
                    // Update textarea for validation
                    if (text) {
                        messageTextarea.setCustomValidity('');
                    } else {
                        messageTextarea.setCustomValidity('Please enter a message.');
                    }
                }
            });
            
            console.log('Quill editor initialized successfully');
        } catch (error) {
            console.error('Error initializing Quill editor:', error);
            showFallbackTextarea();
        }
        
        // Form validation and submission
        setupFormValidation();
    }
    
    function setupFormValidation() {
        const form = document.getElementById('gpEmailForm');
        const sendBtn = document.getElementById('sendEmailBtn');
        
        if (form) {
            // Remove existing listeners to avoid duplicates
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
            
            newForm.addEventListener('submit', function(e) {
                const subject = document.getElementById('subject');
                const subjectValue = subject ? subject.value.trim() : '';
                const messageTextarea = document.getElementById('message');
                let message = '';
                
                // Get message from Quill editor or textarea
                if (quillEditor) {
                    const text = quillEditor.getText().trim();
                    const html = quillEditor.root.innerHTML;
                    
                    if (!text) {
                        e.preventDefault();
                        alert('Please enter a message.');
                        quillEditor.focus();
                        return false;
                    }
                    
                    // Update hidden textarea with HTML content
                    if (messageTextarea) {
                        messageTextarea.value = html;
                    }
                    message = text;
                } else if (messageTextarea) {
                    message = messageTextarea.value.trim();
                }
                
                if (!subjectValue || !message) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }
                
                // Disable button to prevent double submission
                if (sendBtn) {
                    sendBtn.disabled = true;
                    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                }
            });
        }
    }
    
    // Start loading Quill when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadQuill, 100);
        });
    } else {
        // DOM is already ready
        setTimeout(loadQuill, 100);
    }
})();
</script>
@endpush
@endsection

