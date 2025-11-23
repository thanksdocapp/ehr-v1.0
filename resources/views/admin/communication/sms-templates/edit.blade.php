@extends('admin.layouts.app')

@section('title', 'Edit SMS Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('sms-templates.index') }}">SMS Templates</a></li>
    <li class="breadcrumb-item active">Edit Template</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-sms me-2 text-primary"></i>Edit SMS Template</h1>
        <p class="page-subtitle text-muted">Update the SMS template for automated text communications</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editSMSTemplateForm" action="{{ contextRoute('sms-templates.update', $smsTemplate) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Template Basic Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Template Information</h4>
                        <small class="opacity-75">Basic template details and configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Template Name *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $smsTemplate->name) }}" 
                                           placeholder="appointment_reminder" required>
                                    <div class="form-help">Use lowercase letters, numbers, and underscores only</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">
                                        <i class="fas fa-folder me-1"></i>Category *
                                    </label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="appointment" {{ old('category', $smsTemplate->category) == 'appointment' ? 'selected' : '' }}>Appointment</option>
                                        <option value="medical" {{ old('category', $smsTemplate->category) == 'medical' ? 'selected' : '' }}>Medical</option>
                                        <option value="welcome" {{ old('category', $smsTemplate->category) == 'welcome' ? 'selected' : '' }}>Welcome</option>
                                        <option value="reminder" {{ old('category', $smsTemplate->category) == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                        <option value="billing" {{ old('category', $smsTemplate->category) == 'billing' ? 'selected' : '' }}>Billing</option>
                                        <option value="pharmacy" {{ old('category', $smsTemplate->category) == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                                        <option value="emergency" {{ old('category', $smsTemplate->category) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                        <option value="general" {{ old('category', $smsTemplate->category) == 'general' ? 'selected' : '' }}>General</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="sender_id" class="form-label">
                                        <i class="fas fa-signature me-1"></i>Sender ID
                                    </label>
                                    <input type="text" class="form-control @error('sender_id') is-invalid @enderror" 
                                           id="sender_id" name="sender_id" value="{{ old('sender_id', $smsTemplate->sender_id ?: 'HOSPITAL') }}" 
                                           placeholder="HOSPITAL" maxlength="11">
                                    <div class="form-help">Maximum 11 characters (leave blank for default)</div>
                                    @error('sender_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status *
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $smsTemplate->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="active" {{ old('status', $smsTemplate->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $smsTemplate->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2" 
                                      placeholder="Brief description of this template">{{ old('description', $smsTemplate->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- SMS Content-->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-sms me-2"></i>SMS Content</h4>
                        <small class="opacity-75">Template body and content configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="message" class="form-label">
                                <i class="fas fa-code me-1"></i>SMS Message *
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="6" required 
                                      placeholder="Enter your SMS message here..." 
                                      oninput="updateCharacterCount()">{{ old('message', $smsTemplate->message) }}</textarea>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="form-help">
                                    You can use variables like @{{patient_name}}, @{{doctor_name}}, @{{appointment_date}}, etc.
                                </small>
                                <div class="text-end">
                                    <span id="charCount" class="badge bg-secondary">0/160</span>
                                    <span id="smsCount" class="badge bg-info">1 SMS</span>
                                </div>
                            </div>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-section text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-save me-2"></i>Update Template
                    </button>
                    <a href="{{ contextRoute('sms-templates.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Template Info</h3>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <strong>Created:</strong>
                        <span>{{ formatDate($smsTemplate->created_at) }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Last Modified:</strong>
                        <span>{{ formatDate($smsTemplate->updated_at) }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Last Used:</strong>
                        <span>{{ $smsTemplate->last_used_at ? $smsTemplate->last_used_at->diffForHumans() : 'Never' }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Current Status:</strong>
                        <span class="badge {{ $smsTemplate->status_badge_class }}">
                            {{ ucfirst($smsTemplate->status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Available Variables</h3>
                </div>
                <div class="card-body">
                    <div class="variable-list">
                        <div class="variable-category">
                            <h6 class="text-primary">Patient Information</h6>
                            <div class="variable-item">
                                <code>@{{patient_name}}</code>
                                <small class="text-muted d-block">Patient's full name</small>
                            </div>
                            <div class="variable-item">
                                <code>@{{patient_id}}</code>
                                <small class="text-muted d-block">Patient ID</small>
                            </div>
                        </div>
                        
                        <div class="variable-category">
                            <h6 class="text-primary">Doctor Information</h6>
                            <div class="variable-item">
                                <code>@{{doctor_name}}</code>
                                <small class="text-muted d-block">Doctor's name</small>
                            </div>
                            <div class="variable-item">
                                <code>@{{doctor_phone}}</code>
                                <small class="text-muted d-block">Doctor's phone number</small>
                            </div>
                        </div>
                        
                        <div class="variable-category">
                            <h6 class="text-primary">Appointment Details</h6>
                            <div class="variable-item">
                                <code>@{{appointment_date}}</code>
                                <small class="text-muted d-block">Appointment date</small>
                            </div>
                            <div class="variable-item">
                                <code>@{{appointment_time}}</code>
                                <small class="text-muted d-block">Appointment time</small>
                            </div>
                        </div>
                        
                        <div class="variable-category">
                            <h6 class="text-primary">Hospital Information</h6>
                            <div class="variable-item">
                                <code>@{{hospital_name}}</code>
                                <small class="text-muted d-block">Hospital name</small>
                            </div>
                            <div class="variable-item">
                                <code>@{{hospital_phone}}</code>
                                <small class="text-muted d-block">Hospital phone</small>
                            </div>
                            <div class="variable-item">
                                <code>@{{hospital_address}}</code>
                                <small class="text-muted d-block">Hospital address</small>
                            </div>
                        </div>
                        
                        <div class="variable-category">
                            <h6 class="text-primary">System Variables</h6>
                            <div class="variable-item">
                                <code>@{{date}}</code>
                                <small class="text-muted d-block">Current date</small>
                            </div>
                            <div class="variable-item">
                                <code>@{{time}}</code>
                                <small class="text-muted d-block">Current time</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
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
    
    .form-help {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.5rem;
        font-style: italic;
    }
    
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .variable-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .variable-category {
        margin-bottom: 1.5rem;
    }
    
    .variable-category h6 {
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .variable-item {
        padding: 0.5rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .variable-item:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }
    
    .variable-item code {
        color: #e83e8c;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .info-item strong {
        min-width: 100px;
        flex-shrink: 0;
    }
    
    .info-item span {
        text-align: right;
        word-break: break-word;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-outline-primary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-secondary {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .sms-preview-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .sms-header {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .sms-content {
        font-size: 1rem;
        line-height: 1.5;
        color: #212529;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .sms-stats {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
    }
    
    .stat-item {
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .stat-item:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
function updateCharacterCount() {
    const textarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const smsCount = document.getElementById('smsCount');
    
    const length = textarea.value.length;
    charCount.textContent = length + '/160';
    
    // Update character count color
    if (length > 160) {
        charCount.className = 'badge bg-danger';
    } else if (length > 140) {
        charCount.className = 'badge bg-warning';
    } else {
        charCount.className = 'badge bg-secondary';
    }
    
    // Calculate SMS count
    let smsCountValue = 1;
    if (length > 160) {
        smsCountValue = Math.ceil(length / 153);
    }
    smsCount.textContent = smsCountValue + ' SMS';
}

function previewTemplate() {
    const message = document.getElementById('message').value;
    const senderId = document.getElementById('sender_id').value || 'HOSPITAL';
    
    if (!message.trim()) {
        alert('Please enter a message first.');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const content = document.getElementById('previewContent');
    
    const sampleData = {
        patient_name: 'John Doe',
        patient_id: 'P001',
        doctor_name: 'Dr. Smith',
        doctor_phone: '+000 123 456 789',
        appointment_date: '2024-01-15',
        appointment_time: '10:00 AM',
        hospital_name: 'ThanksDoc EHR',
        hospital_phone: '+000 123 456 789',
        hospital_address: '123 Healthcare Avenue, Accra',
        date: new Date().toISOString().split('T')[0],
        time: new Date().toLocaleTimeString()
    };
    
    // Replace variables in message
    let previewMessage = message;
    Object.keys(sampleData).forEach(key => {
        const regex = new RegExp('{{' + key + '}}', 'g');
        previewMessage = previewMessage.replace(regex, sampleData[key]);
    });
    
    const length = previewMessage.length;
    const smsCountValue = length <= 160 ? 1 : Math.ceil(length / 153);
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <div class="sms-preview-box">
                    <div class="sms-header">
                        <strong>From:</strong> ${senderId}
                    </div>
                    <div class="sms-content">
                        ${previewMessage}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sms-stats">
                    <div class="stat-item">
                        <strong>Character Count:</strong> ${length}
                    </div>
                    <div class="stat-item">
                        <strong>SMS Count:</strong> ${smsCountValue}
                    </div>
                    <div class="stat-item">
                        <strong>Sender ID:</strong> ${senderId}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    modal.show();
}

// Initialize character count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCharacterCount();
    
    // Add click handlers for variable items
    document.querySelectorAll('.variable-item').forEach(item => {
        item.addEventListener('click', function() {
            const variable = this.querySelector('code').textContent;
            const textarea = document.getElementById('message');
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);
            
            textarea.value = textBefore + variable + textAfter;
            textarea.focus();
            textarea.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);
            
            updateCharacterCount();
        });
    });
});
</script>
@endpush
