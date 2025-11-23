@extends('admin.layouts.app')

@section('title', 'Email Notification Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.email-management.index') }}">Email Management</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@push('styles')
<style>
.settings-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.settings-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.settings-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px 15px 0 0;
    position: relative;
}

.settings-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    border-radius: 15px 15px 0 0;
}

.settings-body {
    padding: 2.5rem;
}

.setting-group {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.setting-group h5 {
    color: #5a5c69;
    margin-bottom: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.setting-group h5 i {
    margin-right: 0.75rem;
    width: 24px;
    text-align: center;
}

.form-check {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    transition: all 0.3s ease;
}

.form-check:hover {
    border-color: #667eea;
    transform: translateX(5px);
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.form-check-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-check-label {
    font-weight: 500;
    color: #5a5c69;
    cursor: pointer;
    flex-grow: 1;
}

.setting-description {
    color: #858796;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.setting-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-enabled {
    background-color: #d4edda;
    color: #155724;
}

.status-disabled {
    background-color: #f8d7da;
    color: #721c24;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1rem 3rem;
    border-radius: 50px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-reset {
    background: linear-gradient(135deg, #858796 0%, #60616f 100%);
    border: none;
    padding: 1rem 3rem;
    border-radius: 50px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(133, 135, 150, 0.3);
}

.btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(133, 135, 150, 0.4);
    color: white;
}

.advanced-settings {
    background: #fff;
    border: 2px dashed #e3e6f0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    color: #858796;
}

.advanced-settings:hover {
    border-color: #667eea;
    color: #667eea;
}

.settings-tip {
    background: linear-gradient(135deg, #36b9cc 0%, #1cc88a 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.settings-tip i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-sliders-h me-2 text-primary"></i>Email Notification Settings</h1>
        <p class="page-subtitle text-muted">Configure automated email notification preferences</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="settingsForm" method="POST" action="{{ route('admin.email-management.settings.update') }}">
                @csrf
                
                <div class="settings-card">
                    <div class="settings-header">
                        <h3 class="mb-0"><i class="fas fa-users me-2"></i>Patient Notifications</h3>
                        <p class="mb-0 opacity-75">Configure automated emails sent to patients</p>
                    </div>
                    <div class="settings-body">
                        <div class="setting-group">
                            <h5><i class="fas fa-user-plus text-primary"></i>Registration & Welcome</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="patient_welcome" 
                                       name="notifications[patient_welcome]" value="1" 
                                       {{ (old('notifications.patient_welcome', $notificationSettings['patient_welcome'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="patient_welcome">
                                    <div>
                                        <strong>Welcome Email</strong>
                                        <div class="setting-description">Send welcome email to new patients with account details and hospital information.</div>
                                    </div>
                                    <span class="setting-status status-enabled">High Priority</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="patient_verification" 
                                       name="notifications[patient_verification]" value="1" 
                                       {{ (old('notifications.patient_verification', $notificationSettings['patient_verification'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="patient_verification">
                                    <div>
                                        <strong>Email Verification</strong>
                                        <div class="setting-description">Send email verification link to new patients to confirm their email address.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Required</span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <h5><i class="fas fa-calendar-alt text-success"></i>Appointment Related</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="appointment_confirmation" 
                                       name="notifications[appointment_confirmation]" value="1" 
                                       {{ (old('notifications.appointment_confirmation', $notificationSettings['appointment_confirmation'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="appointment_confirmation">
                                    <div>
                                        <strong>Appointment Confirmation</strong>
                                        <div class="setting-description">Send confirmation email when appointments are booked or rescheduled.</div>
                                    </div>
                                    <span class="setting-status status-enabled">High Priority</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="appointment_reminder" 
                                       name="notifications[appointment_reminder]" value="1" 
                                       {{ (old('notifications.appointment_reminder', $notificationSettings['appointment_reminder'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="appointment_reminder">
                                    <div>
                                        <strong>Appointment Reminders</strong>
                                        <div class="setting-description">Send reminder emails 24 hours before scheduled appointments.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Medium Priority</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="appointment_cancellation" 
                                       name="notifications[appointment_cancellation]" value="1" 
                                       {{ (old('notifications.appointment_cancellation', $notificationSettings['appointment_cancellation'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="appointment_cancellation">
                                    <div>
                                        <strong>Cancellation Notice</strong>
                                        <div class="setting-description">Send notification when appointments are cancelled by hospital or doctor.</div>
                                    </div>
                                    <span class="setting-status status-enabled">High Priority</span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <h5><i class="fas fa-vial text-info"></i>Medical Results & Reports</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="test_results" 
                                       name="notifications[test_results]" value="1" 
                                       {{ (old('notifications.test_results', $notificationSettings['test_results'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="test_results">
                                    <div>
                                        <strong>Test Results Available</strong>
                                        <div class="setting-description">Notify patients when laboratory or diagnostic test results are ready.</div>
                                    </div>
                                    <span class="setting-status status-enabled">High Priority</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="prescription_ready" 
                                       name="notifications[prescription_ready]" value="1" 
                                       {{ (old('notifications.prescription_ready', $notificationSettings['prescription_ready'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="prescription_ready">
                                    <div>
                                        <strong>Prescription Ready</strong>
                                        <div class="setting-description">Send notification when prescriptions are ready for pickup at pharmacy.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Medium Priority</span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <h5><i class="fas fa-credit-card text-warning"></i>Billing & Payment</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="payment_reminder" 
                                       name="notifications[payment_reminder]" value="1" 
                                       {{ (old('notifications.payment_reminder', $notificationSettings['payment_reminder'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="payment_reminder">
                                    <div>
                                        <strong>Payment Reminders</strong>
                                        <div class="setting-description">Send reminders for outstanding bills and payment due dates.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Medium Priority</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="payment_confirmation" 
                                       name="notifications[payment_confirmation]" value="1" 
                                       {{ (old('notifications.payment_confirmation', $notificationSettings['payment_confirmation'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="payment_confirmation">
                                    <div>
                                        <strong>Payment Confirmation</strong>
                                        <div class="setting-description">Send receipt and confirmation emails for successful payments.</div>
                                    </div>
                                    <span class="setting-status status-enabled">High Priority</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-card mt-4">
                    <div class="settings-header">
                        <h3 class="mb-0"><i class="fas fa-user-md me-2"></i>Staff Notifications</h3>
                        <p class="mb-0 opacity-75">Configure internal notifications for hospital staff</p>
                    </div>
                    <div class="settings-body">
                        <div class="setting-group">
                            <h5><i class="fas fa-user-plus text-primary"></i>Patient Registration</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="new_patient_registration" 
                                       name="staff_notifications[new_patient_registration]" value="1" 
                                       {{ (old('staff_notifications.new_patient_registration', $staffNotificationSettings['new_patient_registration'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="new_patient_registration">
                                    <div>
                                        <strong>New Patient Registration</strong>
                                        <div class="setting-description">Notify admissions staff when new patients register online.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Medium Priority</span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <h5><i class="fas fa-calendar-check text-success"></i>Appointment Management</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="new_appointment" 
                                       name="staff_notifications[new_appointment]" value="1" 
                                       {{ (old('staff_notifications.new_appointment', $staffNotificationSettings['new_appointment'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="new_appointment">
                                    <div>
                                        <strong>New Appointment Bookings</strong>
                                        <div class="setting-description">Notify doctors and staff about new appointment bookings.</div>
                                    </div>
                                    <span class="setting-status status-enabled">High Priority</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="appointment_changes" 
                                       name="staff_notifications[appointment_changes]" value="1" 
                                       {{ (old('staff_notifications.appointment_changes', $staffNotificationSettings['appointment_changes'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="appointment_changes">
                                    <div>
                                        <strong>Appointment Changes</strong>
                                        <div class="setting-description">Notify staff when appointments are rescheduled or cancelled.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Medium Priority</span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-group">
                            <h5><i class="fas fa-exclamation-triangle text-danger"></i>Critical Alerts</h5>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="emergency_admission" 
                                       name="staff_notifications[emergency_admission]" value="1" 
                                       {{ (old('staff_notifications.emergency_admission', $staffNotificationSettings['emergency_admission'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="emergency_admission">
                                    <div>
                                        <strong>Emergency Admissions</strong>
                                        <div class="setting-description">Immediate notification for emergency patient admissions.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Critical</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="critical_results" 
                                       name="staff_notifications[critical_results]" value="1" 
                                       {{ (old('staff_notifications.critical_results', $staffNotificationSettings['critical_results'] ?? true)) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between align-items-start" for="critical_results">
                                    <div>
                                        <strong>Critical Lab Results</strong>
                                        <div class="setting-description">Urgent notification for abnormal or critical test results.</div>
                                    </div>
                                    <span class="setting-status status-enabled">Critical</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 mb-5">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-save me-3">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                        <button type="button" class="btn btn-reset" id="resetSettings">
                            <i class="fas fa-undo me-2"></i>Reset to Defaults
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="settings-tip">
                <div class="text-center">
                    <i class="fas fa-lightbulb"></i>
                    <h5 class="mt-2">Pro Tips</h5>
                    <p class="mb-0">Enable critical notifications to ensure important medical information reaches patients and staff promptly.</p>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-body">
                    <h5><i class="fas fa-chart-line text-primary me-2"></i>Current Usage</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h3 class="text-success">1,247</h3>
                                <small class="text-muted">Emails Today</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h3 class="text-primary">97.2%</h3>
                            <small class="text-muted">Success Rate</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <a href="{{ route('admin.email-management.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>View Email Logs
                        </a>
                    </div>
                </div>
            </div>

            <div class="advanced-settings mt-4">
                <i class="fas fa-cogs fa-2x mb-3"></i>
                <h5>Advanced Settings</h5>
                <p class="mb-3">Configure SMTP settings, email templates, and delivery options.</p>
                <a href="{{ route('admin.email-config') }}" class="btn btn-outline-primary">
                    <i class="fas fa-cog me-2"></i>Email Configuration
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form submission
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Settings Saved!',
                        text: response.message || 'Your notification settings have been updated successfully.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to save settings. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.message || 'Failed to save settings. Please try again.',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Reset to defaults
    $('#resetSettings').on('click', function() {
        Swal.fire({
            title: 'Reset to Default Settings?',
            text: 'This will restore all notification settings to their default values.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reset',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#858796'
        }).then((result) => {
            if (result.isConfirmed) {
                // Check all checkboxes (default enabled)
                $('#settingsForm input[type="checkbox"]').each(function() {
                    // Set critical and high priority items to checked by default
                    const label = $(this).siblings('label');
                    const hasCritical = label.find('.setting-status').text().includes('Critical');
                    const hasHighPriority = label.find('.setting-status').text().includes('High Priority');
                    const hasRequired = label.find('.setting-status').text().includes('Required');
                    
                    if (hasCritical || hasHighPriority || hasRequired) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', true); // For demo, enable all by default
                    }
                });
                
                Swal.fire({
                    icon: 'success',
                    title: 'Settings Reset',
                    text: 'All settings have been reset to default values. Don\'t forget to save your changes.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    
    // Form validation feedback
    $('#settingsForm input[type="checkbox"]').on('change', function() {
        const form = $('#settingsForm');
        const checkedBoxes = form.find('input[type="checkbox"]:checked').length;
        
        if (checkedBoxes === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Notifications Enabled',
                text: 'You have disabled all email notifications. Patients and staff will not receive any automated emails.',
                confirmButtonText: 'I Understand'
            });
        }
    });
    
    // Smooth animations for form elements
    $('.form-check').hover(
        function() {
            $(this).find('.form-check-label').css('color', '#667eea');
        },
        function() {
            $(this).find('.form-check-label').css('color', '#5a5c69');
        }
    );
});
</script>
@endpush
