@extends('admin.layouts.app')

@section('title', 'Add New Prescription')

@php
    use Illuminate\Support\Str;
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('prescriptions.index') }}">Prescriptions</a></li>
    <li class="breadcrumb-item active">Add New Prescription</li>
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

.form-control {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}

.medication-item {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    position: relative;
}

.medication-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e3e6f0;
}

.remove-medication {
    position: absolute;
    top: 10px;
    right: 10px;
}

.patient-info-card {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.patient-info-card h6 {
    color: white;
    margin-bottom: 1rem;
}

.patient-info-card .info-item {
    margin-bottom: 0.5rem;
}

.patient-info-card .info-label {
    font-weight: 600;
    opacity: 0.9;
}

.patient-info-card .info-value {
    opacity: 0.8;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2"></i>Add New Prescription</h5>
        <small class="text-muted">Create a new prescription</small>
        <p class="page-subtitle text-muted">Create a new prescription with medication details and instructions</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createPrescriptionForm" method="POST" action="{{ contextRoute('prescriptions.store') }}">
                @csrf
                
                <!-- Patient and Doctor Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                        <small class="opacity-75">Select patient and prescribing doctor</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>Patient *
                                    </label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @if(isset($patients))
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" 
                                                        data-patient-info="{{ json_encode([
                                                            'name' => $patient->full_name,
                                                            'patient_id' => $patient->patient_id,
                                                            'age' => $patient->age,
                                                            'gender' => $patient->gender,
                                                            'blood_group' => $patient->blood_group,
                                                            'phone' => $patient->phone,
                                                            'allergies' => $patient->allergies
                                                        ]) }}"
                                                        {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->full_name }} ({{ $patient->patient_id }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doctor_id" class="form-label">
                                        <i class="fas fa-user-md me-1"></i>Prescribing Doctor *
                                    </label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                            id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        @if(isset($doctors))
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    Dr. {{ $doctor->full_name }} - {{ $doctor->specialization }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Patient Info Display -->
                        <div id="patient-info-display" style="display: none;">
                            <div class="patient-info-card">
                                <h6><i class="fas fa-user me-2"></i>Patient Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span class="info-label">Name:</span> 
                                            <span class="info-value" id="patient-name">-</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Patient ID:</span> 
                                            <span class="info-value" id="patient-id-display">-</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Age:</span> 
                                            <span class="info-value" id="patient-age">-</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span class="info-label">Gender:</span> 
                                            <span class="info-value" id="patient-gender">-</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Blood Group:</span> 
                                            <span class="info-value" id="patient-blood">-</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Phone:</span> 
                                            <span class="info-value" id="patient-phone">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item" id="patient-allergies-container" style="display: none;">
                                    <span class="info-label">Allergies:</span> 
                                    <span class="info-value text-warning" id="patient-allergies">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prescription Details Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Prescription Details</h4>
                        <small class="opacity-75">General prescription information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prescription_date" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Prescription Date *
                                    </label>
                                    <input type="text" class="form-control @error('prescription_date') is-invalid @enderror" 
                                           id="prescription_date" name="prescription_date" 
                                           value="{{ old('prescription_date', date('d-m-Y')) }}" 
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10" required>
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('prescription_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prescription_type" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Prescription Type *
                                    </label>
                                    <select class="form-control @error('prescription_type') is-invalid @enderror" 
                                            id="prescription_type" name="prescription_type" required>
                                        <option value="">Select Type</option>
                                        <option value="new" {{ old('prescription_type', 'new') == 'new' ? 'selected' : '' }}>New Prescription</option>
                                        <option value="refill" {{ old('prescription_type') == 'refill' ? 'selected' : '' }}>Refill</option>
                                        <option value="modification" {{ old('prescription_type') == 'modification' ? 'selected' : '' }}>Modification</option>
                                        <option value="emergency" {{ old('prescription_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                    @error('prescription_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pharmacy_name" class="form-label">
                                        <i class="fas fa-store me-1"></i>Pharmacy Name
                                    </label>
                                    <input type="text" class="form-control @error('pharmacy_name') is-invalid @enderror" 
                                           id="pharmacy_name" name="pharmacy_name" value="{{ old('pharmacy_name') }}" 
                                           placeholder="Enter pharmacy name">
                                    @error('pharmacy_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pharmacy_contact" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Pharmacy Contact
                                    </label>
                                    <input type="text" class="form-control @error('pharmacy_contact') is-invalid @enderror" 
                                           id="pharmacy_contact" name="pharmacy_contact" value="{{ old('pharmacy_contact') }}" 
                                           placeholder="Enter pharmacy contact">
                                    @error('pharmacy_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="diagnosis" class="form-label">
                                <i class="fas fa-diagnoses me-1"></i>Diagnosis
                            </label>
                            <textarea class="form-control @error('diagnosis') is-invalid @enderror" 
                                      id="diagnosis" name="diagnosis" rows="3" 
                                      placeholder="Enter diagnosis or reason for prescription">{{ old('diagnosis') }}</textarea>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Additional Notes
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Enter any additional notes or instructions">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Medications Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-pills me-2"></i>Medications</h4>
                        <small class="opacity-75">Add medications with dosage and instructions</small>
                    </div>
                    <div class="form-section-body">
                        <div id="medications-container">
                            @if(old('medications'))
                                @foreach(old('medications') as $index => $medication)
                                    <div class="medication-item">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-medication">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <div class="medication-header">
                                            <h6><i class="fas fa-pill me-2"></i>Medication {{ $index + 1 }}</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Medication Name *</label>
                                                    <input type="text" class="form-control" name="medications[{{ $index }}][name]" 
                                                           value="{{ $medication['name'] ?? '' }}" placeholder="Enter medication name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Dosage *</label>
                                                    <input type="text" class="form-control" name="medications[{{ $index }}][dosage]" 
                                                           value="{{ $medication['dosage'] ?? '' }}" placeholder="e.g., 500mg, 2 tablets" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Frequency *</label>
                                                    <select class="form-control" name="medications[{{ $index }}][frequency]" required>
                                                        <option value="">Select Frequency</option>
                                                        <option value="Once daily" {{ ($medication['frequency'] ?? '') == 'Once daily' ? 'selected' : '' }}>Once daily</option>
                                                        <option value="Twice daily" {{ ($medication['frequency'] ?? '') == 'Twice daily' ? 'selected' : '' }}>Twice daily</option>
                                                        <option value="Three times daily" {{ ($medication['frequency'] ?? '') == 'Three times daily' ? 'selected' : '' }}>Three times daily</option>
                                                        <option value="Four times daily" {{ ($medication['frequency'] ?? '') == 'Four times daily' ? 'selected' : '' }}>Four times daily</option>
                                                        <option value="Every 4 hours" {{ ($medication['frequency'] ?? '') == 'Every 4 hours' ? 'selected' : '' }}>Every 4 hours</option>
                                                        <option value="Every 6 hours" {{ ($medication['frequency'] ?? '') == 'Every 6 hours' ? 'selected' : '' }}>Every 6 hours</option>
                                                        <option value="Every 8 hours" {{ ($medication['frequency'] ?? '') == 'Every 8 hours' ? 'selected' : '' }}>Every 8 hours</option>
                                                        <option value="Every 12 hours" {{ ($medication['frequency'] ?? '') == 'Every 12 hours' ? 'selected' : '' }}>Every 12 hours</option>
                                                        <option value="As needed" {{ ($medication['frequency'] ?? '') == 'As needed' ? 'selected' : '' }}>As needed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Duration</label>
                                                    <input type="text" class="form-control" name="medications[{{ $index }}][duration]" 
                                                           value="{{ $medication['duration'] ?? '' }}" placeholder="e.g., 7 days, 2 weeks">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Special Instructions</label>
                                            <textarea class="form-control" name="medications[{{ $index }}][instructions]" rows="2" 
                                                      placeholder="e.g., Take with food, Take before meals">{{ $medication['instructions'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="medication-item">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-medication">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div class="medication-header">
                                        <h6><i class="fas fa-pill me-2"></i>Medication 1</h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Medication Name *</label>
                                                <input type="text" class="form-control" name="medications[0][name]" 
                                                       placeholder="Enter medication name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Dosage *</label>
                                                <input type="text" class="form-control" name="medications[0][dosage]" 
                                                       placeholder="e.g., 500mg, 2 tablets" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Frequency *</label>
                                                <select class="form-control" name="medications[0][frequency]" required>
                                                    <option value="">Select Frequency</option>
                                                    <option value="Once daily">Once daily</option>
                                                    <option value="Twice daily">Twice daily</option>
                                                    <option value="Three times daily">Three times daily</option>
                                                    <option value="Four times daily">Four times daily</option>
                                                    <option value="Every 4 hours">Every 4 hours</option>
                                                    <option value="Every 6 hours">Every 6 hours</option>
                                                    <option value="Every 8 hours">Every 8 hours</option>
                                                    <option value="Every 12 hours">Every 12 hours</option>
                                                    <option value="As needed">As needed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Duration</label>
                                                <input type="text" class="form-control" name="medications[0][duration]" 
                                                       placeholder="e.g., 7 days, 2 weeks">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Special Instructions</label>
                                        <textarea class="form-control" name="medications[0][instructions]" rows="2" 
                                                  placeholder="e.g., Take with food, Take before meals"></textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-medication">
                            <i class="fas fa-plus me-1"></i>Add Another Medication
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Prescription
                        </button>
                        <a href="{{ contextRoute('prescriptions.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Prescription Guidelines</h6>
                <ul>
                    <li>All fields marked with * are required</li>
                    <li>Select patient and doctor first</li>
                    <li>Add at least one medication</li>
                    <li>Specify accurate dosage and frequency</li>
                    <li>Include special instructions if needed</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                <ul>
                    <li>Double-check medication names</li>
                    <li>Verify dosage calculations</li>
                    <li>Consider drug interactions</li>
                    <li>Include clear instructions</li>
                    <li>Specify treatment duration</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Safety Reminders</h6>
                <ul>
                    <li><strong>Allergies:</strong> Check patient allergies</li>
                    <li><strong>Interactions:</strong> Verify drug interactions</li>
                    <li><strong>Dosage:</strong> Confirm appropriate dosing</li>
                    <li><strong>Duration:</strong> Specify treatment length</li>
                    <li><strong>Follow-up:</strong> Schedule review appointments</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('prescriptions.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Prescriptions
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="validate-form">
                        <i class="fas fa-check me-1"></i>Validate Form
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" id="clear-form">
                        <i class="fas fa-refresh me-1"></i>Clear Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Date input mask for dd-mm-yyyy format
    $('#prescription_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        $(this).val(value);
    });

    // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission
    $('form').on('submit', function() {
        const prescriptionDateInput = $('#prescription_date');
        const dateStr = prescriptionDateInput.val();
        if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
            const parts = dateStr.split('-');
            const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
            prescriptionDateInput.val(yyyyMmDd);
        }
    });
    let medicationIndex = {{ old('medications') ? count(old('medications')) : 1 }};

    // Patient selection change handler
    $('#patient_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const patientInfo = selectedOption.data('patient-info');
            if (patientInfo) {
                $('#patient-name').text(patientInfo.name || '-');
                $('#patient-id-display').text(patientInfo.patient_id || '-');
                $('#patient-age').text(patientInfo.age ? patientInfo.age + ' years' : '-');
                $('#patient-gender').text(patientInfo.gender ? patientInfo.gender.charAt(0).toUpperCase() + patientInfo.gender.slice(1) : '-');
                $('#patient-blood').text(patientInfo.blood_group || '-');
                $('#patient-phone').text(patientInfo.phone || '-');
                
                if (patientInfo.allergies && patientInfo.allergies.length > 0) {
                    $('#patient-allergies').text(Array.isArray(patientInfo.allergies) ? patientInfo.allergies.join(', ') : patientInfo.allergies);
                    $('#patient-allergies-container').show();
                } else {
                    $('#patient-allergies-container').hide();
                }
                
                $('#patient-info-display').slideDown();
            }
        } else {
            $('#patient-info-display').slideUp();
        }
    });

    // Add medication functionality
    $('#add-medication').click(function() {
        const medicationHtml = `
            <div class="medication-item">
                <button type="button" class="btn btn-outline-danger btn-sm remove-medication">
                    <i class="fas fa-trash"></i>
                </button>
                <div class="medication-header">
                    <h6><i class="fas fa-pill me-2"></i>Medication ${medicationIndex + 1}</h6>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Medication Name *</label>
                            <input type="text" class="form-control" name="medications[${medicationIndex}][name]" 
                                   placeholder="Enter medication name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Dosage *</label>
                            <input type="text" class="form-control" name="medications[${medicationIndex}][dosage]" 
                                   placeholder="e.g., 500mg, 2 tablets" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Frequency *</label>
                            <select class="form-control" name="medications[${medicationIndex}][frequency]" required>
                                <option value="">Select Frequency</option>
                                <option value="Once daily">Once daily</option>
                                <option value="Twice daily">Twice daily</option>
                                <option value="Three times daily">Three times daily</option>
                                <option value="Four times daily">Four times daily</option>
                                <option value="Every 4 hours">Every 4 hours</option>
                                <option value="Every 6 hours">Every 6 hours</option>
                                <option value="Every 8 hours">Every 8 hours</option>
                                <option value="Every 12 hours">Every 12 hours</option>
                                <option value="As needed">As needed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="medications[${medicationIndex}][duration]" 
                                   placeholder="e.g., 7 days, 2 weeks">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Special Instructions</label>
                    <textarea class="form-control" name="medications[${medicationIndex}][instructions]" rows="2" 
                              placeholder="e.g., Take with food, Take before meals"></textarea>
                </div>
            </div>
        `;
        $('#medications-container').append(medicationHtml);
        medicationIndex++;
        updateMedicationNumbers();
    });

    // Remove medication functionality
    $(document).on('click', '.remove-medication', function() {
        if ($('.medication-item').length > 1) {
            $(this).closest('.medication-item').slideUp(300, function() {
                $(this).remove();
                updateMedicationNumbers();
            });
        } else {
            alert('At least one medication is required.');
        }
    });

    // Update medication numbers
    function updateMedicationNumbers() {
        $('.medication-item').each(function(index) {
            $(this).find('.medication-header h6').html(`<i class="fas fa-pill me-2"></i>Medication ${index + 1}`);
        });
    }

    // Form validation
    $('#validate-form').click(function() {
        let errors = [];
        
        if (!$('#patient_id').val()) errors.push('Patient selection is required');
        if (!$('#doctor_id').val()) errors.push('Doctor selection is required');
        if (!$('#prescription_date').val()) errors.push('Prescription date is required');
        
        let validMedications = 0;
        $('.medication-item').each(function() {
            const name = $(this).find('input[name*="[name]"]').val();
            const dosage = $(this).find('input[name*="[dosage]"]').val();
            const frequency = $(this).find('select[name*="[frequency]"]').val();
            
            if (name && dosage && frequency) {
                validMedications++;
            }
        });
        
        if (validMedications === 0) {
            errors.push('At least one complete medication is required');
        }
        
        if (errors.length > 0) {
            alert('Please fix the following errors:\\n' + errors.join('\\n'));
        } else {
            alert('Form validation passed!');
        }
    });

    // Clear form
    $('#clear-form').click(function() {
        if (confirm('Are you sure you want to clear the form? All entered data will be lost.')) {
            $('#createPrescriptionForm')[0].reset();
            $('#patient-info-display').hide();
            
            // Reset medications to single item
            $('#medications-container').html(`
                <div class="medication-item">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-medication">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="medication-header">
                        <h6><i class="fas fa-pill me-2"></i>Medication 1</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Medication Name *</label>
                                <input type="text" class="form-control" name="medications[0][name]" 
                                       placeholder="Enter medication name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Dosage *</label>
                                <input type="text" class="form-control" name="medications[0][dosage]" 
                                       placeholder="e.g., 500mg, 2 tablets" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Frequency *</label>
                                <select class="form-control" name="medications[0][frequency]" required>
                                    <option value="">Select Frequency</option>
                                    <option value="Once daily">Once daily</option>
                                    <option value="Twice daily">Twice daily</option>
                                    <option value="Three times daily">Three times daily</option>
                                    <option value="Four times daily">Four times daily</option>
                                    <option value="Every 4 hours">Every 4 hours</option>
                                    <option value="Every 6 hours">Every 6 hours</option>
                                    <option value="Every 8 hours">Every 8 hours</option>
                                    <option value="Every 12 hours">Every 12 hours</option>
                                    <option value="As needed">As needed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Duration</label>
                                <input type="text" class="form-control" name="medications[0][duration]" 
                                       placeholder="e.g., 7 days, 2 weeks">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Special Instructions</label>
                        <textarea class="form-control" name="medications[0][instructions]" rows="2" 
                                  placeholder="e.g., Take with food, Take before meals"></textarea>
                    </div>
                </div>
            `);
            medicationIndex = 1;
        }
    });

    // Trigger patient info display if patient is already selected (in case of validation errors)
    if ($('#patient_id').val()) {
        $('#patient_id').trigger('change');
    }
});
</script>
@endpush
