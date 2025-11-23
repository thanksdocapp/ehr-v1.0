<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title mb-0">
            <i class="fas fa-code me-2 text-primary"></i>Available Template Variables
        </h3>
        <small class="text-muted">Click on any variable to insert it into the editor</small>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleSampleValues">
                <i class="fas fa-eye me-1"></i>Show Sample Values
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="variable-list">
            <!-- Template Name Examples -->
            <div class="variable-category">
                <h6 class="text-primary">
                    <i class="fas fa-file-alt me-1"></i>Template Name Examples
                </h6>
                <div class="template-name-examples">
                    <small class="text-muted d-block mb-2">Common template naming patterns:</small>
                    <div class="variable-item template-example">
                        <code>appointment_confirmation</code>
                        <small class="text-muted d-block">Appointment confirmation emails</small>
                    </div>
                    <div class="variable-item template-example">
                        <code>appointment_reminder</code>
                        <small class="text-muted d-block">Appointment reminder emails</small>
                    </div>
                    <div class="variable-item template-example">
                        <code>prescription_ready</code>
                        <small class="text-muted d-block">Prescription ready notifications</small>
                    </div>
                    <div class="variable-item template-example">
                        <code>lab_results_available</code>
                        <small class="text-muted d-block">Lab results notifications</small>
                    </div>
                    <div class="variable-item template-example">
                        <code>billing_statement</code>
                        <small class="text-muted d-block">Billing and invoice emails</small>
                    </div>
                    <div class="variable-item template-example">
                        <code>welcome_new_patient</code>
                        <small class="text-muted d-block">New patient welcome emails</small>
                    </div>
                </div>
            </div>
            
            <!-- Patient Information -->
            <div class="variable-category">
                <h6 class="text-primary">
                    <i class="fas fa-user me-1"></i>Patient Information
                </h6>
                <div class="variable-item" data-variable="patient_name" data-sample="John Doe">
                    <code>{!!'{{'!!}patient_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pname{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's full name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_first_name" data-sample="John">
                    <code>{!!'{{'!!}patient_first_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pfn{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's first name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_last_name" data-sample="Doe">
                    <code>{!!'{{'!!}patient_last_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pln{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's last name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_id" data-sample="P001234">
                    <code>{!!'{{'!!}patient_id{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pid{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient ID number</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_email" data-sample="john.doe@example.com">
                    <code>{!!'{{'!!}patient_email{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pemail{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's email address</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_phone" data-sample="+000 123 456 789">
                    <code>{!!'{{'!!}patient_phone{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pphone{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's phone number</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_age" data-sample="45 years">
                    <code>{!!'{{'!!}patient_age{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}page{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's age</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_gender" data-sample="Male">
                    <code>{!!'{{'!!}patient_gender{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pgender{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's gender</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_address" data-sample="123 Oak Street, Accra, Ghana">
                    <code>{!!'{{'!!}patient_address{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}paddress{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's full address</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="patient_date_of_birth" data-sample="March 15, 1978">
                    <code>{!!'{{'!!}patient_date_of_birth{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}pdob{!!'}}'!!}</span>
                    <small class="text-muted d-block">Patient's date of birth</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
            </div>
            
            <!-- Doctor Information -->
            <div class="variable-category">
                <h6 class="text-primary">
                    <i class="fas fa-user-md me-1"></i>Doctor Information
                </h6>
                <div class="variable-item" data-variable="doctor_name" data-sample="Dr. Sarah Johnson">
                    <code>{!!'{{'!!}doctor_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dname{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's full name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_first_name" data-sample="Sarah">
                    <code>{!!'{{'!!}doctor_first_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dfn{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's first name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_last_name" data-sample="Johnson">
                    <code>{!!'{{'!!}doctor_last_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dln{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's last name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_title" data-sample="Dr.">
                    <code>{!!'{{'!!}doctor_title{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dtitle{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's title (Dr., Prof., etc.)</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_specialization" data-sample="Cardiology">
                    <code>{!!'{{'!!}doctor_specialization{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dspec{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's specialization</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_phone" data-sample="+000 123 456 700">
                    <code>{!!'{{'!!}doctor_phone{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dphone{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's phone number</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_email" data-sample="dr.johnson@hospital.com">
                    <code>{!!'{{'!!}doctor_email{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}demail{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's email address</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="doctor_qualification" data-sample="MD, PhD, FACC">
                    <code>{!!'{{'!!}doctor_qualification{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dqual{!!'}}'!!}</span>
                    <small class="text-muted d-block">Doctor's qualifications</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
            </div>
            
            <!-- Appointment Details -->
            <div class="variable-category">
                <h6 class="text-primary">
                    <i class="fas fa-calendar-alt me-1"></i>Appointment Details
                </h6>
                <div class="variable-item" data-variable="appointment_id" data-sample="APT001234">
                    <code>{!!'{{'!!}appointment_id{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}aid{!!'}}'!!}</span>
                    <small class="text-muted d-block">Unique appointment ID</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_date" data-sample="January 15, 2024">
                    <code>{!!'{{'!!}appointment_date{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}adate{!!'}}'!!}</span>
                    <small class="text-muted d-block">Appointment date</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_time" data-sample="10:30 AM">
                    <code>{!!'{{'!!}appointment_time{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}atime{!!'}}'!!}</span>
                    <small class="text-muted d-block">Appointment time</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_datetime" data-sample="January 15, 2024 at 10:30 AM">
                    <code>{!!'{{'!!}appointment_datetime{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}adt{!!'}}'!!}</span>
                    <small class="text-muted d-block">Full appointment date & time</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_duration" data-sample="30 minutes">
                    <code>{!!'{{'!!}appointment_duration{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}adur{!!'}}'!!}</span>
                    <small class="text-muted d-block">Appointment duration</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_type" data-sample="Consultation">
                    <code>{!!'{{'!!}appointment_type{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}atype{!!'}}'!!}</span>
                    <small class="text-muted d-block">Type of appointment</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_status" data-sample="Confirmed">
                    <code>{!!'{{'!!}appointment_status{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}astatus{!!'}}'!!}</span>
                    <small class="text-muted d-block">Current appointment status</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="department" data-sample="Cardiology Department">
                    <code>{!!'{{'!!}department{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}dept{!!'}}'!!}</span>
                    <small class="text-muted d-block">Hospital department</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="room_number" data-sample="Room 305">
                    <code>{!!'{{'!!}room_number{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}room{!!'}}'!!}</span>
                    <small class="text-muted d-block">Room/office number</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="floor" data-sample="3rd Floor">
                    <code>{!!'{{'!!}floor{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}floor{!!'}}'!!}</span>
                    <small class="text-muted d-block">Floor number</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="appointment_notes" data-sample="Please arrive 15 minutes early for check-in">
                    <code>{!!'{{'!!}appointment_notes{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}anotes{!!'}}'!!}</span>
                    <small class="text-muted d-block">Special appointment notes</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
            </div>
            
            <!-- Hospital Information -->
            <div class="variable-category">
                <h6 class="text-primary">
                    <i class="fas fa-hospital me-1"></i>Hospital Information
                </h6>
                <div class="variable-item" data-variable="hospital_name" data-sample="ThankDoc EHR">
                    <code>{!!'{{'!!}hospital_name{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}hname{!!'}}'!!}</span>
                    <small class="text-muted d-block">Hospital/clinic name</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="hospital_phone" data-sample="+000 302 123 456">
                    <code>{!!'{{'!!}hospital_phone{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}hphone{!!'}}'!!}</span>
                    <small class="text-muted d-block">Main hospital phone</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="hospital_email" data-sample="info@newwaveshospital.com">
                    <code>{!!'{{'!!}hospital_email{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}hemail{!!'}}'!!}</span>
                    <small class="text-muted d-block">Hospital contact email</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="hospital_address" data-sample="123 Healthcare Avenue, Accra, Ghana">
                    <code>{!!'{{'!!}hospital_address{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}haddress{!!'}}'!!}</span>
                    <small class="text-muted d-block">Hospital full address</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="hospital_website" data-sample="https://www.newwaveshospital.com">
                    <code>{!!'{{'!!}hospital_website{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}hsite{!!'}}'!!}</span>
                    <small class="text-muted d-block">Hospital website URL</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
                <div class="variable-item" data-variable="emergency_number" data-sample="+000 302 EMERGENCY">
                    <code>{!!'{{'!!}emergency_number{!!'}}'!!}</code> <span class="badge bg-secondary ms-1">{!!'{{'!!}emnum{!!'}}'!!}</span>
                    <small class="text-muted d-block">Emergency contact number</small>
                    <div class="sample-value mt-1" style="display: none; color: #28a745; font-weight: 500;">Sample: <span class="sample-text"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSampleValues');
    const sampleValues = document.querySelectorAll('.sample-value');
    let showingSamples = false;

    // Populate sample text from data attributes
    document.querySelectorAll('.variable-item[data-sample]').forEach(item => {
        const sampleText = item.querySelector('.sample-text');
        if (sampleText) {
            sampleText.textContent = item.getAttribute('data-sample');
        }
    });

    toggleBtn.addEventListener('click', function() {
        showingSamples = !showingSamples;
        
        sampleValues.forEach(sample => {
            sample.style.display = showingSamples ? 'block' : 'none';
        });
        
        // Update button text and icon
        const icon = toggleBtn.querySelector('i');
        const text = showingSamples ? 'Hide Sample Values' : 'Show Sample Values';
        toggleBtn.innerHTML = showingSamples 
            ? '<i class="fas fa-eye-slash me-1"></i>' + text
            : '<i class="fas fa-eye me-1"></i>' + text;
    });

    // Add click handlers for inserting variables into editor
    document.querySelectorAll('.variable-item code').forEach(code => {
        code.style.cursor = 'pointer';
        code.addEventListener('click', function() {
            const variableText = this.textContent;
            
            // Try to insert into active editor (TinyMCE, CodeMirror, or textarea)
            if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
                tinyMCE.activeEditor.insertContent(variableText);
            } else {
                // Find the active textarea or input field
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'TEXTAREA' || activeElement.tagName === 'INPUT') {
                    const start = activeElement.selectionStart;
                    const end = activeElement.selectionEnd;
                    const value = activeElement.value;
                    
                    activeElement.value = value.substring(0, start) + variableText + value.substring(end);
                    activeElement.selectionStart = activeElement.selectionEnd = start + variableText.length;
                    activeElement.focus();
                }
            }
            
            // Visual feedback
            this.style.backgroundColor = '#28a745';
            this.style.color = 'white';
            setTimeout(() => {
                this.style.backgroundColor = '';
                this.style.color = '';
            }, 200);
        });
    });
});
</script>

<style>
.variable-item {
    padding: 8px 12px;
    margin-bottom: 8px;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    background: #f8f9fc;
    transition: all 0.2s ease;
}

.variable-item:hover {
    background: #e3e6f0;
    border-color: #5a5c69;
}

.variable-item code {
    background: #667eea;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.85em;
    transition: all 0.2s ease;
}

.variable-item code:hover {
    background: #5a6fd8;
    transform: translateY(-1px);
}

.variable-category {
    margin-bottom: 20px;
}

.variable-category h6 {
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e3e6f0;
}

.template-example {
    margin-bottom: 6px;
}

.sample-value {
    font-size: 0.85em;
    font-style: italic;
}
</style>
