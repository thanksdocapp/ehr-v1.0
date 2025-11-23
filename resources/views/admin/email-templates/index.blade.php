@extends('admin.layouts.app')

@section('title', 'Email Templates Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">Email Templates Management</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#previewModal">
                        <i class="fas fa-eye"></i> Preview Templates
                    </button>
                    <a href="{{ contextRoute('email-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Categories -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="templateTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="appointment-tab" data-bs-toggle="tab" data-bs-target="#appointment" type="button" role="tab">
                                <i class="fas fa-calendar-check"></i> Appointment Templates
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="patient-tab" data-bs-toggle="tab" data-bs-target="#patient" type="button" role="tab">
                                <i class="fas fa-user"></i> Patient Templates
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab">
                                <i class="fas fa-bell"></i> Notification Templates
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="marketing-tab" data-bs-toggle="tab" data-bs-target="#marketing" type="button" role="tab">
                                <i class="fas fa-bullhorn"></i> Marketing Templates
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="templateTabsContent">
                        <!-- Appointment Templates -->
                        <div class="tab-pane fade show active" id="appointment" role="tabpanel">
                            <div class="row">
                                @foreach($templates['appointment'] ?? [] as $template)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title">{{ $template['name'] }}</h5>
                                                <span class="badge bg-{{ $template['status'] == 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($template['status']) }}
                                                </span>
                                            </div>
                                            <p class="card-text text-muted small">{{ $template['description'] }}</p>
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> Last updated: {{ $template['updated_at'] }}
                                                </small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('{{ $template['id'] }}')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <a href="{{ contextRoute('email-templates.edit', $template['id']) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate('{{ $template['id'] }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                
                                <!-- Default appointment templates if none exist -->
                                @if(empty($templates['appointment']))
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Appointment Confirmation</h5>
                                            <p class="card-text text-muted small">Email sent to patients when appointment is confirmed</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('appointment-confirmation')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Appointment Reminder</h5>
                                            <p class="card-text text-muted small">Email sent 24 hours before appointment</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('appointment-reminder')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Appointment Cancellation</h5>
                                            <p class="card-text text-muted small">Email sent when appointment is cancelled</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('appointment-cancellation')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Patient Templates -->
                        <div class="tab-pane fade" id="patient" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Welcome Email</h5>
                                            <p class="card-text text-muted small">Email sent to new patients after registration</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('patient-welcome')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Password Reset</h5>
                                            <p class="card-text text-muted small">Email sent when patient requests password reset</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('password-reset')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Test Results</h5>
                                            <p class="card-text text-muted small">Email sent when test results are ready</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('test-results')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Templates -->
                        <div class="tab-pane fade" id="notification" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">System Maintenance</h5>
                                            <p class="card-text text-muted small">Email sent during system maintenance</p>
                                            <div class="mb-3">
                                                <span class="badge bg-secondary">Inactive</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('system-maintenance')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Emergency Alert</h5>
                                            <p class="card-text text-muted small">Email sent for emergency notifications</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('emergency-alert')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marketing Templates -->
                        <div class="tab-pane fade" id="marketing" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Health Newsletter</h5>
                                            <p class="card-text text-muted small">Monthly health tips and updates</p>
                                            <div class="mb-3">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('health-newsletter')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Promotional Offers</h5>
                                            <p class="card-text text-muted small">Special offers and discounts</p>
                                            <div class="mb-3">
                                                <span class="badge bg-secondary">Inactive</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="previewTemplate('promotional-offers')">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['total_templates'] ?? 12 }}</h4>
                            <p class="mb-0">Total Templates</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['active_templates'] ?? 9 }}</h4>
                            <p class="mb-0">Active Templates</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['emails_sent_today'] ?? 156 }}</h4>
                            <p class="mb-0">Emails Sent Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['open_rate'] ?? 78 }}%</h4>
                            <p class="mb-0">Average Open Rate</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Email Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Email Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Template</th>
                                    <th>Recipient</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 10; $i++)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">Appointment Confirmation</span>
                                    </td>
                                    <td>patient{{ $i + 1 }}@example.com</td>
                                    <td>Your appointment is confirmed</td>
                                    <td>
                                        <span class="badge bg-success">Delivered</span>
                                    </td>
                                    <td>{{ formatDateTime(now()->subMinutes(rand(5, 120))) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewEmail({{ $i + 1 }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="resendEmail({{ $i + 1 }})">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Preview Modal -->
<div class="modal fade" id="templatePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="templatePreviewContent">
                    <!-- Template content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">Send Test Email</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Templates Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Templates Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="list-group" id="templateList">
                            <a href="#" class="list-group-item list-group-item-action active" data-template="appointment-confirmation">
                                <i class="fas fa-calendar-check"></i> Appointment Confirmation
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-template="appointment-reminder">
                                <i class="fas fa-clock"></i> Appointment Reminder
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-template="patient-welcome">
                                <i class="fas fa-user-plus"></i> Patient Welcome
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-template="password-reset">
                                <i class="fas fa-key"></i> Password Reset
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-template="test-results">
                                <i class="fas fa-file-medical"></i> Test Results
                            </a>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="templatePreview">
                            <!-- Template preview will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.template-card {
    border: 1px solid #e3e6f0;
    transition: transform 0.2s, box-shadow 0.2s;
}

.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #007bff;
    background-color: transparent;
}

.bg-primary {
    background-color: #007bff !important;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
}

.bg-info {
    background-color: #17a2b8 !important;
}

#templatePreview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    min-height: 400px;
    background-color: #f8f9fa;
}

.list-group-item {
    border-left: 3px solid transparent;
}

.list-group-item.active {
    border-left-color: #007bff;
}

.email-preview {
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 2rem;
    font-family: Arial, sans-serif;
    line-height: 1.6;
}

.email-header {
    border-bottom: 2px solid #007bff;
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

.email-footer {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 2rem;
    font-size: 0.875rem;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize template preview
    $('#previewModal').on('shown.bs.modal', function() {
        loadTemplatePreview('appointment-confirmation');
    });

    // Template list click handlers
    $('#templateList .list-group-item').on('click', function(e) {
        e.preventDefault();
        $('#templateList .list-group-item').removeClass('active');
        $(this).addClass('active');
        const template = $(this).data('template');
        loadTemplatePreview(template);
    });
});

function previewTemplate(templateId) {
    // Load template preview content
    const templates = {
        'appointment-confirmation': {
            subject: 'Appointment Confirmation - City Hospital',
            content: `
                <div class="email-preview">
                    <div class="email-header">
                        <h2 style="color: #007bff; margin: 0;">City Hospital</h2>
                        <p style="margin: 0; color: #6c757d;">Your Healthcare Partner</p>
                    </div>
                    <h3>Appointment Confirmation</h3>
                    <p>Dear {{patient_name}},</p>
                    <p>Your appointment has been confirmed with the following details:</p>
                    <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0;">
                        <p><strong>Doctor:</strong> {{doctor_name}}</p>
                        <p><strong>Date:</strong> {{appointment_date}}</p>
                        <p><strong>Time:</strong> {{appointment_time}}</p>
                        <p><strong>Department:</strong> {{department}}</p>
                    </div>
                    <p>Please arrive 15 minutes before your scheduled time.</p>
                    <p>If you need to reschedule, please contact us at least 24 hours in advance.</p>
                    <div class="email-footer">
                        <p>Best regards,<br>City Hospital Team</p>
                        <p>Phone: (555) 123-4567 | Email: info@cityhospital.com</p>
                    </div>
                </div>
            `
        },
        'appointment-reminder': {
            subject: 'Appointment Reminder - Tomorrow',
            content: `
                <div class="email-preview">
                    <div class="email-header">
                        <h2 style="color: #007bff; margin: 0;">City Hospital</h2>
                        <p style="margin: 0; color: #6c757d;">Your Healthcare Partner</p>
                    </div>
                    <h3>Appointment Reminder</h3>
                    <p>Dear {{patient_name}},</p>
                    <p>This is a friendly reminder about your upcoming appointment:</p>
                    <div style="background-color: #fff3cd; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0; border-left: 4px solid #ffc107;">
                        <p><strong>Tomorrow at {{appointment_time}}</strong></p>
                        <p><strong>Doctor:</strong> {{doctor_name}}</p>
                        <p><strong>Department:</strong> {{department}}</p>
                    </div>
                    <p>Please bring:</p>
                    <ul>
                        <li>Your ID card</li>
                        <li>Insurance card</li>
                        <li>Any relevant medical records</li>
                    </ul>
                    <div class="email-footer">
                        <p>Best regards,<br>City Hospital Team</p>
                        <p>Phone: (555) 123-4567 | Email: info@cityhospital.com</p>
                    </div>
                </div>
            `
        },
        'patient-welcome': {
            subject: 'Welcome to City Hospital',
            content: `
                <div class="email-preview">
                    <div class="email-header">
                        <h2 style="color: #007bff; margin: 0;">Welcome to City Hospital</h2>
                        <p style="margin: 0; color: #6c757d;">Your Healthcare Partner</p>
                    </div>
                    <h3>Welcome, {{patient_name}}!</h3>
                    <p>Thank you for choosing City Hospital for your healthcare needs.</p>
                    <p>Your patient account has been successfully created. You can now:</p>
                    <ul>
                        <li>Schedule appointments online</li>
                        <li>View your medical records</li>
                        <li>Receive test results</li>
                        <li>Access our patient portal</li>
                    </ul>
                    <div style="background-color: #d4edda; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0; border-left: 4px solid #28a745;">
                        <p><strong>Your Patient ID:</strong> {{patient_id}}</p>
                        <p><strong>Login Email:</strong> {{patient_email}}</p>
                    </div>
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                    <div class="email-footer">
                        <p>Best regards,<br>City Hospital Team</p>
                        <p>Phone: (555) 123-4567 | Email: info@cityhospital.com</p>
                    </div>
                </div>
            `
        }
    };

    const template = templates[templateId];
    if (template) {
        $('#templatePreviewContent').html(template.content);
        $('#templatePreviewModal').modal('show');
    }
}

function loadTemplatePreview(templateId) {
    const templates = {
        'appointment-confirmation': {
            subject: 'Appointment Confirmation - City Hospital',
            content: `
                <div class="email-preview">
                    <div class="email-header">
                        <h2 style="color: #007bff; margin: 0;">City Hospital</h2>
                        <p style="margin: 0; color: #6c757d;">Your Healthcare Partner</p>
                    </div>
                    <h3>Appointment Confirmation</h3>
                    <p>Dear {{patient_name}},</p>
                    <p>Your appointment has been confirmed with the following details:</p>
                    <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0;">
                        <p><strong>Doctor:</strong> {{doctor_name}}</p>
                        <p><strong>Date:</strong> {{appointment_date}}</p>
                        <p><strong>Time:</strong> {{appointment_time}}</p>
                        <p><strong>Department:</strong> {{department}}</p>
                    </div>
                    <p>Please arrive 15 minutes before your scheduled time.</p>
                    <div class="email-footer">
                        <p>Best regards,<br>City Hospital Team</p>
                    </div>
                </div>
            `
        },
        'appointment-reminder': {
            subject: 'Appointment Reminder - Tomorrow',
            content: `
                <div class="email-preview">
                    <div class="email-header">
                        <h2 style="color: #007bff; margin: 0;">City Hospital</h2>
                        <p style="margin: 0; color: #6c757d;">Your Healthcare Partner</p>
                    </div>
                    <h3>Appointment Reminder</h3>
                    <p>Dear {{patient_name}},</p>
                    <p>This is a friendly reminder about your upcoming appointment tomorrow.</p>
                    <div class="email-footer">
                        <p>Best regards,<br>City Hospital Team</p>
                    </div>
                </div>
            `
        },
        'patient-welcome': {
            subject: 'Welcome to City Hospital',
            content: `
                <div class="email-preview">
                    <div class="email-header">
                        <h2 style="color: #007bff; margin: 0;">Welcome to City Hospital</h2>
                        <p style="margin: 0; color: #6c757d;">Your Healthcare Partner</p>
                    </div>
                    <h3>Welcome, {{patient_name}}!</h3>
                    <p>Thank you for choosing City Hospital for your healthcare needs.</p>
                    <div class="email-footer">
                        <p>Best regards,<br>City Hospital Team</p>
                    </div>
                </div>
            `
        }
    };

    const template = templates[templateId] || templates['appointment-confirmation'];
    $('#templatePreview').html(template.content);
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template?')) {
        // Handle template deletion
        console.log('Deleting template:', templateId);
    }
}

function sendTestEmail() {
    const email = prompt('Enter email address to send test email:');
    if (email) {
        alert('Test email sent to ' + email);
        $('#templatePreviewModal').modal('hide');
    }
}

function viewEmail(emailId) {
    // Handle email view
    console.log('Viewing email:', emailId);
}

function resendEmail(emailId) {
    if (confirm('Are you sure you want to resend this email?')) {
        // Handle email resend
        console.log('Resending email:', emailId);
    }
}
</script>
@endpush
