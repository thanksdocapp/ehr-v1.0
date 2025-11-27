@extends('admin.layouts.app')

@section('title', 'Edit Prescription')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('prescriptions.index') }}">Prescriptions</a></li>
    <li class="breadcrumb-item active">Edit Prescription</li>
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
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    border-bottom: 2px solid #e2e8f0;
}

.form-section-header h4,
.form-section-header h5 {
    color: #1a202c;
    font-weight: 700;
}

.form-section-header i {
    color: #1a202c;
}

.form-section-header small {
    color: #4a5568;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2"></i>Edit Prescription</h5>
        <small class="text-muted">Update prescription information</small>
        <p class="page-subtitle text-muted">Update prescription details and medications</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editPrescriptionForm" method="POST" action="{{ contextRoute('prescriptions.update', $prescription->id) }}">
                @csrf
                @method('PUT')
                
                <!-- Prescription Details Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                        <small class="opacity-75">Update patient and prescribing doctor details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>Patient *</label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" 
                                                    {{ (old('patient_id', $prescription->patient_id) == $patient->id) ? 'selected' : '' }}>
                                                {{ $patient->full_name }} ({{ $patient->patient_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="doctor_id" class="form-label">
                                        <i class="fas fa-stethoscope me-1"></i>Doctor *</label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                            id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" 
                                                    {{ (old('doctor_id', $prescription->doctor_id) == $doctor->id) ? 'selected' : '' }}>
                                                Dr. {{ $doctor->full_name }} - {{ $doctor->specialization }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prescription_type" class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>Prescription Type *</label>
                                    <select class="form-control @error('prescription_type') is-invalid @enderror" 
                                            id="prescription_type" name="prescription_type" required>
                                        <option value="">Select Type</option>
                                        <option value="new" {{ old('prescription_type', $prescription->prescription_type) == 'new' ? 'selected' : '' }}>New</option>
                                        <option value="refill" {{ old('prescription_type', $prescription->prescription_type) == 'refill' ? 'selected' : '' }}>Refill</option>
                                        <option value="modification" {{ old('prescription_type', $prescription->prescription_type) == 'modification' ? 'selected' : '' }}>Modification</option>
                                        <option value="emergency" {{ old('prescription_type', $prescription->prescription_type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                    @error('prescription_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="prescription_date" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Prescription Date *</label>
                                    <input type="text" class="form-control @error('prescription_date') is-invalid @enderror" 
                                           id="prescription_date" name="prescription_date" 
                                           value="{{ old('prescription_date', formatDate($prescription->prescription_date)) }}" 
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10" required>
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('prescription_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medication Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-pills me-2"></i>Medications</h4>
                        <small class="opacity-75">Detail prescribed medications, dosages, and instructions</small>
                    </div>
                    <div class="form-section-body" id="medications-section">
                        @foreach(old('medications', $prescription->medications) as $index => $medication)
                        <div class="form-group medication-item">
                            <div class="mb-3">
                                <label class="form-label">Medication Name *</label>
                                <input type="text" class="form-control @error('medications.'.$index.'.name') is-invalid @enderror" 
                                       name="medications[{{ $index }}][name]" value="{{ $medication['name'] }}" required>
                                @error('medications.'.$index.'.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dosage *</label>
                                <input type="text" class="form-control @error('medications.'.$index.'.dosage') is-invalid @enderror" 
                                       name="medications[{{ $index }}][dosage]" value="{{ $medication['dosage'] }}" required>
                                @error('medications.'.$index.'.dosage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Frequency *</label>
                                <input type="text" class="form-control @error('medications.'.$index.'.frequency') is-invalid @enderror" 
                                       name="medications[{{ $index }}][frequency]" value="{{ $medication['frequency'] }}" required>
                                @error('medications.'.$index.'.frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Duration *</label>
                                <input type="text" class="form-control @error('medications.'.$index.'.duration') is-invalid @enderror" 
                                       name="medications[{{ $index }}][duration]" value="{{ $medication['duration'] }}" required>
                                @error('medications.'.$index.'.duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instructions (Optional)</label>
                                <textarea class="form-control"
                                          name="medications[{{ $index }}][instructions]" rows="2">{{ $medication['instructions'] }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Prescription
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
                <h6><i class="fas fa-info-circle me-2"></i>Prescription Information</h6>
                <ul>
                    <li><strong>Prescription ID:</strong> {{ $prescription->id }}</li>
                    <li><strong>Creation:</strong> {{ formatDate($prescription->created_at) }}</li>
                    <li><strong>Last Update:</strong> {{ formatDateTime($prescription->updated_at) }}</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Prescription Guidelines</h6>
                <ul>
                    <li>Ensure accurate dosage and frequency</li>
                    <li>Provide clear medication instructions</li>
                    <li>Update doctor and patient details</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security</h6>
                <ul>
                    <li><strong>GDPR Compliant:</strong> All records protected</li>
                    <li><strong>Access Control:</strong> Role-based permissions</li>
                    <li><strong>Audit Trail:</strong> All changes tracked</li>
                </ul>
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
    $('#medications-section').on('click', '.add-medication', function() {
        // Logic to add new medication fields dynamically
    });

    $('#editPrescriptionForm').on('submit', function(e) {
        // Form validation logic
    });
});
</script>
@endpush

