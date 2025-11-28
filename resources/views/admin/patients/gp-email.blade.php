@extends('layouts.admin')

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
            <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-secondary">
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
                    <form action="{{ route('admin.patients.gp-email.send', $patient) }}" method="POST" id="gpEmailForm" enctype="multipart/form-data">
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
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="10" 
                                      required
                                      placeholder="Enter your message to the GP..."
                                      style="border: 2px solid #e2e8f0; border-radius: 6px; resize: vertical;">{{ old('message') }}</textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Patient information will be automatically included in the email.
                            </small>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Medical Records Selection -->
                        <div class="mb-4">
                            <label for="medical_record_ids" class="form-label" style="color: #2d3748; font-weight: 500;">
                                <i class="fas fa-file-medical me-2"></i>Attach Medical Records (Optional)
                            </label>
                            <select class="form-control @error('medical_record_ids') is-invalid @enderror" 
                                    id="medical_record_ids" 
                                    name="medical_record_ids[]" 
                                    multiple
                                    style="border: 2px solid #e2e8f0; border-radius: 6px; min-height: 120px;">
                                @php
                                    $medicalRecords = \App\Models\MedicalRecord::where('patient_id', $patient->id)
                                        ->with(['doctor', 'appointment'])
                                        ->orderBy('record_date', 'desc')
                                        ->orderBy('created_at', 'desc')
                                        ->get();
                                @endphp
                                @foreach($medicalRecords as $record)
                                    <option value="{{ $record->id }}" {{ in_array($record->id, old('medical_record_ids', [])) ? 'selected' : '' }}>
                                        {{ $record->record_date ? $record->record_date->format('M d, Y') : $record->created_at->format('M d, Y') }} - 
                                        {{ ucfirst($record->record_type) }}
                                        @if($record->doctor)
                                            - Dr. {{ $record->doctor->full_name }}
                                        @endif
                                        @if($record->diagnosis)
                                            - {{ Str::limit($record->diagnosis, 50) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Hold Ctrl (or Cmd on Mac) to select multiple records. All attachments from selected records will be included.
                            </small>
                            @error('medical_record_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File Uploads -->
                        <div class="mb-4">
                            <label for="attachments" class="form-label" style="color: #2d3748; font-weight: 500;">
                                <i class="fas fa-paperclip me-2"></i>Upload Documents (Optional)
                            </label>
                            <input type="file" 
                                   class="form-control @error('attachments.*') is-invalid @enderror" 
                                   id="attachments" 
                                   name="attachments[]" 
                                   multiple
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt,.xls,.xlsx"
                                   style="border: 2px solid #e2e8f0; border-radius: 6px;">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>You can upload multiple files. Accepted formats: PDF, Word, Excel, Images, Text files. Max size: 10MB per file.
                            </small>
                            <div id="fileList" class="mt-2"></div>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-secondary">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('gpEmailForm');
    const sendBtn = document.getElementById('sendEmailBtn');
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('fileList');
    
    // Display selected files
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            if (this.files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-unstyled mb-0';
                Array.from(this.files).forEach(function(file) {
                    const li = document.createElement('li');
                    li.className = 'mb-1';
                    li.innerHTML = '<i class="fas fa-file me-2"></i>' + file.name + ' <small class="text-muted">(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</small>';
                    list.appendChild(li);
                });
                fileList.appendChild(list);
            }
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!subject || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Validate file sizes
            if (fileInput && fileInput.files.length > 0) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                for (let i = 0; i < fileInput.files.length; i++) {
                    if (fileInput.files[i].size > maxSize) {
                        e.preventDefault();
                        alert('File "' + fileInput.files[i].name + '" exceeds the maximum size of 10MB.');
                        return false;
                    }
                }
            }
            
            // Disable button to prevent double submission
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        });
    }
});
</script>
@endpush
@endsection

