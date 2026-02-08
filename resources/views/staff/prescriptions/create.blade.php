@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create Prescription')
@section('page-title', 'Create Prescription')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Create new prescriptions for your patients' : (auth()->user()->role === 'pharmacist' ? 'Create and dispense prescriptions' : 'Create prescriptions with doctor supervision'))

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-prescription-bottle-alt me-2 text-primary"></i>Create Prescription
                    </h1>
                    <p class="text-muted mb-0">
                        @if(auth()->user()->role === 'doctor')
                            Create new prescriptions for your patients
                        @elseif(auth()->user()->role === 'pharmacist')
                            Create and dispense prescriptions
                        @else
                            Create prescriptions with doctor supervision
                        @endif
                    </p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.prescriptions.index') }}">Prescriptions</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.prescriptions.store') }}" method="POST" id="prescriptionForm">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-group mb-3">
                            <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       id="patientSearchInput"
                                       placeholder="Search patient by name or phone…"
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
                                            data-patient-info="{{ json_encode([
                                                'name' => $patient->first_name . ' ' . $patient->last_name,
                                                'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : 'N/A',
                                                'gender' => $patient->gender,
                                                'blood_group' => $patient->blood_group ?? 'Unknown',
                                                'phone' => $patient->phone ?? 'No phone',
                                                'allergies' => $patient->allergies ?? 'None listed'
                                            ]) }}"
                                            {{ old('patient_id', $selectedPatientId ?? null) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone ?? 'No phone' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <a href="{{ route('staff.patients.create') }}" class="text-decoration-none">
                                    <i class="fas fa-plus"></i> Add new patient
                                </a>
                            </small>
                        </div>

                        <div id="patientInfoCard" class="doctor-card mb-3" style="display: none;">
                            <div class="doctor-card-header">
                                <h6 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Patient Details</h6>
                            </div>
                            <div class="doctor-card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Name:</small>
                                        <div class="fw-bold" id="patientName">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Age/Gender:</small>
                                        <div class="fw-bold" id="patientAgeGender">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Blood Group:</small>
                                        <div class="fw-bold" id="patientBloodGroup">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Phone:</small>
                                        <div class="fw-bold" id="patientPhone">-</div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">Allergies:</small>
                                        <div class="fw-bold text-danger" id="patientAllergies">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="medical_record_id" class="form-label">Related Medical Record</label>
                            <select class="form-control @error('medical_record_id') is-invalid @enderror" 
                                    id="medical_record_id" name="medical_record_id">
                                <option value="">No Medical Record (Optional)</option>
                                @foreach($medicalRecords as $record)
                                    <option value="{{ $record->id }}" 
                                            data-patient-id="{{ $record->patient_id }}"
                                            data-diagnosis="{{ $record->diagnosis ?? '' }}"
                                            {{ old('medical_record_id', $selectedMedicalRecordId ?? null) == $record->id ? 'selected' : '' }}>
                                        {{ $record->patient->first_name }} {{ $record->patient->last_name }} - 
                                        {{ $record->created_at->format('M d, Y') }} 
                                        ({{ $record->presenting_complaint ?? $record->chief_complaint ?? ($record->assessment ?? 'No diagnosis') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('medical_record_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                @if(isset($selectedMedicalRecordId) && $selectedMedicalRecordId)
                                    <span class="text-success">Medical record pre-selected</span>
                                @else
                                    Link this prescription to a medical record (optional)
                                @endif
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                @if(auth()->user()->role === 'doctor')
                                    <div class="form-group mb-3">
                                        <label class="form-label">Prescribing Doctor</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ formatDoctorName(auth()->user()->name) }}</div>
                                                    <small class="text-muted">{{ auth()->user()->specialization ?? (auth()->user()->doctor->specialization ?? 'GP') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $currentDoctor = \DB::table('doctors')->where('user_id', auth()->user()->id)->first();
                                        @endphp
                                        <input type="hidden" name="doctor_id" value="{{ $currentDoctor ? $currentDoctor->id : '' }}">
                                    </div>
                                @else
                                    <div class="form-group mb-3">
                                        <label for="doctor_id" class="form-label">Supervising Doctor <span class="text-danger">*</span></label>
                                        <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                                id="doctor_id" name="doctor_id" required>
                                            <option value="">Select Doctor</option>
                                            @foreach(\App\Models\User::where('role', 'doctor')->get() as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    {{ formatDoctorName($doctor->name) }} - {{ $doctor->specialization ?? 'General' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Prescription requires doctor authorization</small>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="prescription_date" class="form-label">Prescription Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control uk-date @error('prescription_date') is-invalid @enderror" 
                                           id="prescription_date" name="prescription_date" 
                                           value="{{ old('prescription_date') ? (old('prescription_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('prescription_date')) ? \Carbon\Carbon::parse(old('prescription_date'))->format('d/m/Y') : old('prescription_date')) : \Carbon\Carbon::now()->format('d/m/Y') }}"
                                           placeholder="dd/mm/yyyy"
                                           pattern="\d{2}/\d{2}/\d{4}"
                                           maxlength="10"
                                           data-default-date="today"
                                           required>
                                    @error('prescription_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="prescription_type" class="form-label">Prescription Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('prescription_type') is-invalid @enderror" 
                                            id="prescription_type" name="prescription_type" required>
                                        <option value="">Select Type</option>
                                        <option value="new" {{ old('prescription_type') === 'new' ? 'selected' : '' }}>New Prescription</option>
                                        <option value="refill" {{ old('prescription_type') === 'refill' ? 'selected' : '' }}>Refill</option>
                                        <option value="modification" {{ old('prescription_type') === 'modification' ? 'selected' : '' }}>Modification</option>
                                        <option value="emergency" {{ old('prescription_type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                    @error('prescription_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-pills me-2"></i>Medications</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div id="medicationsContainer">
                            <!-- Medication items will be added here -->
                        </div>

                        <button type="button" class="btn btn-outline-primary" id="addMedicationBtn">
                            <i class="fas fa-plus me-1"></i>Add Medication
                        </button>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-group mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control @error('diagnosis') is-invalid @enderror" 
                                      id="diagnosis" name="diagnosis" rows="3" 
                                      placeholder="Primary diagnosis or condition...">{{ old('diagnosis') }}</textarea>
                            <div class="autocomplete-suggestions" id="diagnosisSuggestionsPrescription" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">Doctor's Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Additional notes or instructions...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Follow-up & Refills -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-calendar-alt me-2"></i>Follow-up & Refills</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="follow_up_date" class="form-label">Follow-up Date</label>
                                    <input type="text" class="form-control uk-date @error('follow_up_date') is-invalid @enderror" 
                                           id="follow_up_date" name="follow_up_date" 
                                           value="{{ old('follow_up_date') ? (old('follow_up_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('follow_up_date')) ? \Carbon\Carbon::parse(old('follow_up_date'))->format('d/m/Y') : old('follow_up_date')) : '' }}"
                                           placeholder="dd/mm/yyyy"
                                           pattern="\d{2}/\d{2}/\d{4}"
                                           maxlength="10"
                                           data-min-date="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('follow_up_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="refills_allowed" class="form-label">Refills Allowed</label>
                                    <select class="form-control @error('refills_allowed') is-invalid @enderror" 
                                            id="refills_allowed" name="refills_allowed">
                                        <option value="0" {{ old('refills_allowed', '0') === '0' ? 'selected' : '' }}>No Refills</option>
                                        <option value="1" {{ old('refills_allowed') === '1' ? 'selected' : '' }}>1 Refill</option>
                                        <option value="2" {{ old('refills_allowed') === '2' ? 'selected' : '' }}>2 Refills</option>
                                        <option value="3" {{ old('refills_allowed') === '3' ? 'selected' : '' }}>3 Refills</option>
                                        <option value="4" {{ old('refills_allowed') === '4' ? 'selected' : '' }}>4 Refills</option>
                                        <option value="5" {{ old('refills_allowed') === '5' ? 'selected' : '' }}>5 Refills</option>
                                        <option value="6" {{ old('refills_allowed') === '6' ? 'selected' : '' }}>6 Refills</option>
                                    </select>
                                    @error('refills_allowed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                @if(auth()->user()->role === 'pharmacist')
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">Initial Status</label>
                                        <select class="form-control @error('status') is-invalid @enderror" 
                                                id="status" name="status">
                                            <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="dispensed" {{ old('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Pharmacists can set initial status</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-prescription-bottle-alt me-1"></i>Create Prescription
                            </button>
                            <a href="{{ route('staff.prescriptions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Doctor Information -->
                @if(auth()->user()->role === 'doctor')
                    <div class="doctor-card mb-4">
                        <div class="doctor-card-header">
                            <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>Doctor Information</h5>
                        </div>
                        <div class="doctor-card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-lg me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ formatDoctorName(auth()->user()->name) }}</div>
                                    <div class="text-muted">{{ auth()->user()->specialization ?? (auth()->user()->doctor->specialization ?? 'GP') }}</div>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Guidelines -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Guidelines</h5>
                    </div>
                    <div class="doctor-card-body">
                        <ul class="mb-0 text-muted small">
                            <li class="mb-2"><strong>Patient Safety:</strong> Always verify patient allergies and current medications</li>
                            <li class="mb-2"><strong>Dosage:</strong> Ensure accurate dosage and frequency for all medications</li>
                            <li class="mb-2"><strong>Drug Interactions:</strong> Check for potential interactions between prescribed medications</li>
                            <li class="mb-2"><strong>Documentation:</strong> Include clear instructions and any special considerations</li>
                            @if(auth()->user()->role !== 'doctor')
                                <li class="mb-2"><strong>Authorization:</strong> Non-doctor prescriptions require doctor approval before dispensing</li>
                            @endif
                            <li><strong>Follow-up:</strong> Schedule appropriate follow-up appointments for monitoring</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Medication Template (Hidden) -->
<div id="medicationTemplate" style="display: none;">
    <div class="doctor-card mb-3 medication-item">
        <div class="doctor-card-header d-flex justify-content-between align-items-center">
            <h6 class="doctor-card-title mb-0"><i class="fas fa-capsules me-2"></i>Medication <span class="medication-number">1</span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-medication">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="doctor-card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Medication Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control medication-name"
                                   name="medications[INDEX][name]"
                                   placeholder="Type to search..." required
                                   autocomplete="off">
                            <button type="button" class="btn btn-outline-primary quincy-search-btn" title="Search Quincy drug database">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <input type="hidden" class="medication-drug-id" name="medications[INDEX][drug_id]" value="">
                        <small class="text-muted quincy-drug-info" style="display: none;"></small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Strength/Dosage <span class="text-danger">*</span></label>
                        <input type="text" class="form-control medication-dosage" 
                               name="medications[INDEX][dosage]" 
                               placeholder="e.g., 500mg, 10ml..." required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Form</label>
                        <select class="form-control medication-form" name="medications[INDEX][form]">
                            <option value="">Select Form</option>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="liquid">Liquid/Syrup</option>
                            <option value="injection">Injection</option>
                            <option value="topical">Topical/Cream</option>
                            <option value="inhaler">Inhaler</option>
                            <option value="drops">Drops</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select class="form-control medication-frequency" name="medications[INDEX][frequency]" required>
                            <option value="">Select Frequency</option>
                            <option value="once_daily">Once daily</option>
                            <option value="twice_daily">Twice daily</option>
                            <option value="three_times_daily">Three times daily</option>
                            <option value="four_times_daily">Four times daily</option>
                            <option value="every_4_hours">Every 4 hours</option>
                            <option value="every_6_hours">Every 6 hours</option>
                            <option value="every_8_hours">Every 8 hours</option>
                            <option value="every_12_hours">Every 12 hours</option>
                            <option value="as_needed">As needed</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Duration <span class="text-danger">*</span></label>
                        <input type="text" class="form-control medication-duration"
                               name="medications[INDEX][duration]"
                               placeholder="e.g., 7 days, 2 weeks..." required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control medication-quantity"
                               name="medications[INDEX][quantity]"
                               placeholder="e.g., 28" min="1" value="1">
                        <small class="text-muted">Number of units to dispense (for Quincy orders)</small>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-group mb-0">
                        <label class="form-label">Instructions</label>
                        <textarea class="form-control medication-instructions" 
                                  name="medications[INDEX][instructions]" rows="2" 
                                  placeholder="Special instructions for taking this medication..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
$(document).ready(function() {
    let medicationCount = 0;

    // ===== Patient search (alerts-style: live filter the select options; preserves data-patient-info) =====
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
            dataset: { ...opt.dataset },
        }));

        const placeholder = cachedOptions[0] || { value: '', label: 'Select Patient', disabled: false, dataset: {} };
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
                if (item.dataset) {
                    for (const [k, v] of Object.entries(item.dataset)) {
                        opt.dataset[k] = v;
                    }
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
                : 'No patients found. Try a different search or clear.';
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
            t = window.setTimeout(applyFilter, 80);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            applyFilter();
            input.focus();
        });

        applyFilter();
    })();

    // Prescription Date UK format (dd/mm/yyyy) with Flatpickr calendar picker
    (function initPrescriptionDatePicker() {
        const prescriptionDateInput = document.getElementById('prescription_date');
        if (!prescriptionDateInput) return;

        // Wait for Flatpickr to be available
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr library not loaded');
            return;
        }

        // Initialize Flatpickr with UK format
        const prescriptionPicker = flatpickr(prescriptionDateInput, {
            dateFormat: "d/m/Y",
            altInput: false,
            altFormat: "d/m/Y",
            locale: {
                firstDayOfWeek: 1 // Monday
            },
            minDate: "today",
            allowInput: true, // Allow manual typing
            clickOpens: true,
            defaultDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                // Ensure format is dd/mm/yyyy
                if (dateStr && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const date = new Date(dateStr);
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear();
                    instance.input.value = dd + '/' + mm + '/' + yyyy;
                }
            }
        });

        // Store instance for easy access
        prescriptionDateInput._flatpickr = prescriptionPicker;
    })();

    // Set default date if empty
    if (!$('#prescription_date').val()) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        $('#prescription_date').val(dd + '/' + mm + '/' + yyyy);
    }

    // Convert dd/mm/yyyy to yyyy-mm-dd before form submission
    const prescriptionForm = document.getElementById('prescriptionForm');
    if (prescriptionForm) {
        prescriptionForm.addEventListener('submit', function(e) {
            const prescriptionDateInput = document.getElementById('prescription_date');
            if (prescriptionDateInput) {
                const dateValue = prescriptionDateInput.value.trim();
                if (dateValue && dateValue.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    const parts = dateValue.split('/');
                    prescriptionDateInput.value = parts[2] + '-' + parts[1] + '-' + parts[0];
                }
            }
        });
    }

    // Patient selection change
    $('#patient_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');

        if (selectedOption.val()) {
            const patientInfo = selectedOption.data('patient-info');

            if (patientInfo) {
                $('#patientName').text(patientInfo.name);
                $('#patientAgeGender').text(patientInfo.age + ' / ' + ucfirst(patientInfo.gender));
                $('#patientBloodGroup').text(patientInfo.blood_group);
                $('#patientPhone').text(patientInfo.phone);
                $('#patientAllergies').text(patientInfo.allergies);

                $('#patientInfoCard').show();
            }

            // Filter medical records for selected patient
            filterMedicalRecords(selectedOption.val());
        } else {
            $('#patientInfoCard').hide();
            // Only clear medical record if not pre-selected
            const currentMedicalRecordId = $('#medical_record_id').val();
            if (!currentMedicalRecordId || $('#medical_record_id option[value="' + currentMedicalRecordId + '"]').data('patient-id') !== selectedOption.val()) {
                $('#medical_record_id').val('');
            }
        }
    });
    
    // Auto-fill diagnosis from medical record
    $('#medical_record_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val() && selectedOption.data('diagnosis')) {
            const diagnosis = selectedOption.data('diagnosis');
            // Only auto-fill if diagnosis field is empty
            if (!$('#diagnosis').val() || $('#diagnosis').val().trim() === '') {
                $('#diagnosis').val(diagnosis);
            }
        }
    });
    
    // Pre-fill from selected medical record if available
    @if(isset($selectedMedicalRecord) && $selectedMedicalRecord)
        $(document).ready(function() {
            // Pre-fill diagnosis
            @if($selectedMedicalRecord->diagnosis)
                if (!$('#diagnosis').val() || $('#diagnosis').val().trim() === '') {
                    $('#diagnosis').val('{{ addslashes($selectedMedicalRecord->diagnosis) }}');
                }
            @endif
        });
    @endif

    // Add medication
    $('#addMedicationBtn').on('click', function() {
        addMedication();
    });

    // Remove medication
    $(document).on('click', '.remove-medication', function() {
        const medicationItem = $(this).closest('.medication-item');
        medicationItem.remove();
        updateMedicationNumbers();
        
        // Ensure at least one medication item is present
        if ($('.medication-item').length === 0) {
            addMedication();
        }
    });

    // Add first medication on load
    addMedication();

    function addMedication() {
        const template = $('#medicationTemplate').html();
        const medicationHtml = template.replace(/INDEX/g, medicationCount);

        const newMedicationItem = $(medicationHtml);
        $('#medicationsContainer').append(newMedicationItem);
        medicationCount++;
        updateMedicationNumbers();
        
        // Initialize autocomplete for the new medication name field
        setTimeout(function() {
            const newMedicationNameField = newMedicationItem.find('.medication-name');
            if (newMedicationNameField.length && typeof initializeMedicationAutocomplete === 'function') {
                initializeMedicationAutocomplete(newMedicationNameField);
                newMedicationNameField.data('autocomplete-initialized', true);
            }
        }, 100);
    }

    function updateMedicationNumbers() {
        $('.medication-item').each(function(index) {
            $(this).find('.medication-number').text(index + 1);

            // Update form field names
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    function filterMedicalRecords(patientId) {
        $('#medical_record_id option').each(function() {
            const option = $(this);
            if (option.val() === '') {
                option.show();
                return;
            }

            // Use data-patient-id attribute for proper filtering
            const recordPatientId = option.data('patient-id');
            if (recordPatientId && recordPatientId.toString() === patientId.toString()) {
                option.show();
            } else {
                option.hide();
            }
        });

        $('#medical_record_id').val('');
    }

    function ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Form validation
    $('#prescriptionForm').on('submit', function(e) {
        let isValid = true;
        let missingFields = [];

        // Get only visible medication items inside the medications container
        const visibleMedicationItems = $('#medicationsContainer .medication-item:visible');
        
        console.log('Number of visible medication items:', visibleMedicationItems.length);
        
        // Check if at least one medication is added
        if (visibleMedicationItems.length === 0) {
            alert('Please add at least one medication to the prescription.');
            e.preventDefault();
            return false;
        }

        // Check main required fields
        const requiredFields = {
            'patient_id': 'Patient',
            'prescription_date': 'Prescription Date',
            'prescription_type': 'Prescription Type'
        };
        
        // Add doctor_id to required fields if user is not a doctor
        @if(auth()->user()->role !== 'doctor')
        requiredFields['doctor_id'] = 'Supervising Doctor';
        @endif

        Object.keys(requiredFields).forEach(function(fieldId) {
            const field = $('#' + fieldId);
            if (field.length && !field.val()) {
                field.addClass('is-invalid');
                missingFields.push(requiredFields[fieldId]);
                isValid = false;
            } else if (field.length) {
                field.removeClass('is-invalid');
            }
        });

        // Validate each visible medication item by iterating through DOM elements
        visibleMedicationItems.each(function(index) {
            const medicationNum = index + 1;
            const medicationItem = $(this);
            
            // Find fields within this specific medication item
            const nameField = medicationItem.find('.medication-name');
            const dosageField = medicationItem.find('.medication-dosage');
            const frequencyField = medicationItem.find('.medication-frequency');
            const durationField = medicationItem.find('.medication-duration');
            
            console.log('Medication', medicationNum, 'fields found:', {
                name: nameField.length, 
                dosage: dosageField.length, 
                frequency: frequencyField.length, 
                duration: durationField.length
            });
            
            console.log('Medication', medicationNum, 'values:', {
                name: nameField.val(), 
                dosage: dosageField.val(), 
                frequency: frequencyField.val(), 
                duration: durationField.val()
            });
            
            // Validate required fields for this medication
            if (!nameField.val() || nameField.val().trim() === '') {
                missingFields.push('Medication ' + medicationNum + ' - Medication Name');
                nameField.addClass('is-invalid');
                isValid = false;
            } else {
                nameField.removeClass('is-invalid');
            }
            
            if (!dosageField.val() || dosageField.val().trim() === '') {
                missingFields.push('Medication ' + medicationNum + ' - Dosage');
                dosageField.addClass('is-invalid');
                isValid = false;
            } else {
                dosageField.removeClass('is-invalid');
            }
            
            if (!frequencyField.val() || frequencyField.val() === '') {
                missingFields.push('Medication ' + medicationNum + ' - Frequency');
                frequencyField.addClass('is-invalid');
                isValid = false;
            } else {
                frequencyField.removeClass('is-invalid');
            }
            
            if (!durationField.val() || durationField.val().trim() === '') {
                missingFields.push('Medication ' + medicationNum + ' - Duration');
                durationField.addClass('is-invalid');
                isValid = false;
            } else {
                durationField.removeClass('is-invalid');
            }
        });

        console.log('Validation result:', {isValid, missingFields});

        if (!isValid) {
            e.preventDefault();
            let errorMessage = 'Please fill in the following required fields:\n\n';
            errorMessage += missingFields.join('\n');
            alert(errorMessage);
            return false;
        }

        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...').prop('disabled', true);
    });

    // Real-time validation
    $(document).on('blur', 'input, select, textarea', function() {
        if ($(this).prop('required') && !$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Set today's date as default
    // Date pickers are now handled by centralized flatpickr-init.js
    // No manual date setting needed - default dates are set via data-default-date attribute

    // Auto-populate common medication instructions
    $(document).on('change', '.medication-frequency', function() {
        const frequency = $(this).val();
        const instructionsField = $(this).closest('.medication-item').find('.medication-instructions');

        if (!instructionsField.val()) {
            let instruction = '';

            switch(frequency) {
                case 'once_daily':
                    instruction = 'Take once daily, preferably at the same time each day.';
                    break;
                case 'twice_daily':
                    instruction = 'Take twice daily, approximately 12 hours apart.';
                    break;
                case 'three_times_daily':
                    instruction = 'Take three times daily with meals.';
                    break;
                case 'as_needed':
                    instruction = 'Take as needed for symptoms. Do not exceed recommended dosage.';
                    break;
            }

            if (instruction) {
                instructionsField.val(instruction);
            }
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 30000);
    
    // Auto-complete for Medication Names (for dynamically added medications too)
    function initializeMedicationAutocomplete(medicationNameField) {
        let medicationTimeout;
        const medicationSuggestionsContainer = $('<div class="autocomplete-suggestions medication-suggestions" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>');
        
        // Append suggestions container to the medication name field's parent
        if (medicationNameField.length) {
            medicationNameField.parent().css('position', 'relative').append(medicationSuggestionsContainer);
            
            medicationNameField.off('input.medicationAutocomplete').on('input.medicationAutocomplete', function() {
                const query = $(this).val().trim();
                
                clearTimeout(medicationTimeout);
                
                if (query.length < 2) {
                    medicationSuggestionsContainer.hide();
                    return;
                }
                
                medicationTimeout = setTimeout(function() {
                    fetch('{{ route("staff.api.suggestions.medication") }}?q=' + encodeURIComponent(query), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const suggestions = Array.isArray(data) ? data : [];
                        
                        if (suggestions.length > 0) {
                            let html = '<ul class="list-unstyled mb-0">';
                            suggestions.forEach(function(suggestion) {
                                html += '<li class="autocomplete-suggestion-item p-2 border-bottom" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'white\'" data-value="' + suggestion.replace(/"/g, '&quot;') + '">' + suggestion + '</li>';
                            });
                            html += '</ul>';
                            medicationSuggestionsContainer.html(html).show();
                            
                            // Handle suggestion click
                            medicationSuggestionsContainer.find('.autocomplete-suggestion-item').on('click', function() {
                                const value = $(this).data('value');
                                medicationNameField.val(value);
                                medicationSuggestionsContainer.hide();
                            });
                        } else {
                            medicationSuggestionsContainer.hide();
                        }
                    })
                    .catch(error => {
                        console.error('Medication suggestions error:', error);
                        medicationSuggestionsContainer.hide();
                    });
                }, 300);
            });
            
            // Handle keyboard navigation
            medicationNameField.off('keydown.medicationAutocomplete').on('keydown.medicationAutocomplete', function(e) {
                const visibleItems = medicationSuggestionsContainer.find('.autocomplete-suggestion-item:visible');
                if (visibleItems.length === 0) return;
                
                const currentIndex = visibleItems.index(visibleItems.filter('.highlighted'));
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    visibleItems.removeClass('highlighted');
                    const nextIndex = currentIndex < visibleItems.length - 1 ? currentIndex + 1 : 0;
                    visibleItems.eq(nextIndex).addClass('highlighted').css('background', '#f8f9fa');
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    visibleItems.removeClass('highlighted');
                    const prevIndex = currentIndex > 0 ? currentIndex - 1 : visibleItems.length - 1;
                    visibleItems.eq(prevIndex).addClass('highlighted').css('background', '#f8f9fa');
                } else if (e.key === 'Enter' && currentIndex >= 0) {
                    e.preventDefault();
                    visibleItems.eq(currentIndex).click();
                } else if (e.key === 'Escape') {
                    medicationSuggestionsContainer.hide();
                }
            });
        }
    }
    
    // Initialize autocomplete for existing medication name fields after page load
    setTimeout(function() {
        $('.medication-name').each(function() {
            if (!$(this).data('autocomplete-initialized')) {
                initializeMedicationAutocomplete($(this));
                $(this).data('autocomplete-initialized', true);
            }
        });
    }, 500);
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.medication-name, .medication-suggestions').length) {
            $('.medication-suggestions').hide();
        }
        if (!$(e.target).closest('#diagnosis, #diagnosisSuggestionsPrescription').length) {
            $('#diagnosisSuggestionsPrescription').hide();
        }
    });
    
    // Auto-complete for Diagnosis in Prescriptions
    let diagnosisTimeoutPrescription;
    const diagnosisInputPrescription = $('#diagnosis');
    const diagnosisSuggestionsContainerPrescription = $('#diagnosisSuggestionsPrescription');
    
    if (diagnosisInputPrescription.length) {
        diagnosisInputPrescription.parent().css('position', 'relative');
        
        diagnosisInputPrescription.on('input', function() {
            const query = $(this).val().trim();
            
            clearTimeout(diagnosisTimeoutPrescription);
            
            if (query.length < 2) {
                diagnosisSuggestionsContainerPrescription.hide();
                return;
            }
            
            diagnosisTimeoutPrescription = setTimeout(function() {
                fetch('{{ route("staff.api.suggestions.diagnosis") }}?q=' + encodeURIComponent(query), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const suggestions = Array.isArray(data) ? data : [];
                    
                    if (suggestions.length > 0) {
                        let html = '<ul class="list-unstyled mb-0">';
                        suggestions.forEach(function(suggestion) {
                            html += '<li class="autocomplete-suggestion-item p-2 border-bottom" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'white\'" data-value="' + suggestion.replace(/"/g, '&quot;') + '">' + suggestion + '</li>';
                        });
                        html += '</ul>';
                        diagnosisSuggestionsContainerPrescription.html(html).show();
                        
                        // Handle suggestion click
                        diagnosisSuggestionsContainerPrescription.find('.autocomplete-suggestion-item').on('click', function() {
                            const value = $(this).data('value');
                            diagnosisInputPrescription.val(value);
                            diagnosisSuggestionsContainerPrescription.hide();
                        });
                    } else {
                        diagnosisSuggestionsContainerPrescription.hide();
                    }
                })
                .catch(error => {
                    console.error('Diagnosis suggestions error:', error);
                    diagnosisSuggestionsContainerPrescription.hide();
                });
            }, 300);
        });
        
        // Handle keyboard navigation for diagnosis
        diagnosisInputPrescription.on('keydown', function(e) {
            const visibleItems = diagnosisSuggestionsContainerPrescription.find('.autocomplete-suggestion-item:visible');
            if (visibleItems.length === 0) return;
            
            const currentIndex = visibleItems.index(visibleItems.filter('.highlighted'));
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                visibleItems.removeClass('highlighted');
                const nextIndex = currentIndex < visibleItems.length - 1 ? currentIndex + 1 : 0;
                visibleItems.eq(nextIndex).addClass('highlighted').css('background', '#f8f9fa');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                visibleItems.removeClass('highlighted');
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : visibleItems.length - 1;
                visibleItems.eq(prevIndex).addClass('highlighted').css('background', '#f8f9fa');
            } else if (e.key === 'Enter' && currentIndex >= 0) {
                e.preventDefault();
                visibleItems.eq(currentIndex).click();
            } else if (e.key === 'Escape') {
                diagnosisSuggestionsContainerPrescription.hide();
            }
        });
    }
    // ============================================
    // Quincy Drug Search Integration
    // ============================================
    let currentMedicationField = null;

    // Quincy search button click
    $(document).on('click', '.quincy-search-btn', function() {
        currentMedicationField = $(this).closest('.form-group').find('.medication-name');
        const currentValue = currentMedicationField.val();
        $('#quincyDrugSearchInput').val(currentValue);
        $('#quincyDrugSearchModal').modal('show');

        if (currentValue && currentValue.length >= 3) {
            searchQuincyDrugs(currentValue);
        }
    });

    // Search input in modal
    let quincySearchTimeout;
    $('#quincyDrugSearchInput').on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(quincySearchTimeout);

        if (query.length < 3) {
            $('#quincyDrugResults').html('<p class="text-muted text-center py-4">Type at least 3 characters to search...</p>');
            return;
        }

        quincySearchTimeout = setTimeout(function() {
            searchQuincyDrugs(query);
        }, 300);
    });

    function searchQuincyDrugs(query) {
        $('#quincyDrugResults').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="text-muted mt-2">Searching Quincy database...</p></div>');

        $.ajax({
            url: '{{ route("staff.quincy.medicines.search") }}',
            method: 'GET',
            data: { query: query, limit: 20 },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    let html = '<div class="list-group">';
                    response.data.forEach(function(drug) {
                        html += `
                            <a href="#" class="list-group-item list-group-item-action quincy-drug-item"
                               data-drug-id="${drug.id || ''}"
                               data-drug-name="${(drug.name || drug.drug_name || '').replace(/"/g, '&quot;')}"
                               data-drug-form="${drug.form || ''}"
                               data-drug-strength="${drug.strength || ''}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">${drug.name || drug.drug_name || 'Unknown'}</h6>
                                        <small class="text-muted">
                                            ${drug.form ? drug.form + ' - ' : ''}
                                            ${drug.strength || ''}
                                            ${drug.manufacturer ? ' | ' + drug.manufacturer : ''}
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">Select</span>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                    $('#quincyDrugResults').html(html);
                } else {
                    $('#quincyDrugResults').html('<p class="text-muted text-center py-4"><i class="fas fa-search me-2"></i>No drugs found matching "' + query + '"</p>');
                }
            },
            error: function(xhr) {
                if (xhr.status === 400 || (xhr.responseJSON && !xhr.responseJSON.success)) {
                    $('#quincyDrugResults').html('<div class="text-center py-4"><i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i><p class="text-muted">Quincy integration is not configured. You can still enter medications manually.</p></div>');
                } else {
                    $('#quincyDrugResults').html('<p class="text-danger text-center py-4">Error searching Quincy database. Please try again.</p>');
                }
            }
        });
    }

    // Select drug from Quincy results
    $(document).on('click', '.quincy-drug-item', function(e) {
        e.preventDefault();
        const drugId = $(this).data('drug-id');
        const drugName = $(this).data('drug-name');
        const drugForm = $(this).data('drug-form');
        const drugStrength = $(this).data('drug-strength');

        if (currentMedicationField) {
            const medicationItem = currentMedicationField.closest('.medication-item');
            currentMedicationField.val(drugName);
            medicationItem.find('.medication-drug-id').val(drugId);

            if (drugStrength) {
                medicationItem.find('.medication-dosage').val(drugStrength);
            }
            if (drugForm) {
                const formSelect = medicationItem.find('.medication-form');
                const formValue = drugForm.toLowerCase();
                if (formSelect.find(`option[value="${formValue}"]`).length) {
                    formSelect.val(formValue);
                }
            }

            // Show Quincy drug info
            const infoEl = medicationItem.find('.quincy-drug-info');
            infoEl.html(`<i class="fas fa-check-circle text-success me-1"></i>Quincy Drug ID: ${drugId}`).show();
        }

        $('#quincyDrugSearchModal').modal('hide');
    });
});
</script>

{{-- Quincy Drug Search Modal --}}
<div class="modal fade" id="quincyDrugSearchModal" tabindex="-1" aria-labelledby="quincyDrugSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="quincyDrugSearchModalLabel">
                    <i class="fas fa-pills me-2"></i>Search Quincy Drug Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="quincyDrugSearchInput" class="form-label">Search Medications</label>
                    <input type="text" class="form-control form-control-lg" id="quincyDrugSearchInput"
                           placeholder="Type medication name (e.g., Amoxicillin, Paracetamol)..." autocomplete="off">
                    <small class="text-muted">Search by medication name or PIP code</small>
                </div>
                <div id="quincyDrugResults" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted text-center py-4">Type at least 3 characters to search...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
.autocomplete-suggestion-item.highlighted {
    background: #f8f9fa !important;
}
.autocomplete-suggestions {
    margin-top: 2px;
}
.autocomplete-suggestion-item:hover {
    background: #f8f9fa !important;
}
.quincy-drug-item:hover {
    background: #f8f9fa;
}
.quincy-drug-item .badge {
    opacity: 0;
    transition: opacity 0.2s;
}
.quincy-drug-item:hover .badge {
    opacity: 1;
}
</style>
@endpush
