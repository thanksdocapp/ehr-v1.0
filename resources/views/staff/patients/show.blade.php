@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Patient Details')
@section('page-title', 'Patient Details')
@section('page-subtitle', 'Complete patient information and medical history')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="fade-in-up">

    <!-- Patient Alert Bar -->
    @include('components.patient-alert-bar', ['patient' => $patient])

    <!-- New patient (is_guest): blocking notice stays visible (sticky + non-dismissable) until core profile is complete -->
    @if($patient->is_guest)
        @php
            $patientInfoCheck = [
                'is_incomplete' => false,
                'missing_fields' => [],
                'missing_count' => 0,
                'recommended_missing_fields' => [],
                'has_recommended_gaps' => false,
                'has_placeholder_info' => false,
            ];
            try {
                $patientInfoCheck = $patient->hasIncompleteInformation();
            } catch (\Exception $e) {
                // DecryptException etc.: skip field list
            }
            $guestProfileBlocking = (bool) ($patientInfoCheck['is_incomplete'] ?? false);
        @endphp
        @if($guestProfileBlocking)
            <div class="alert alert-persistent clinical-profile-gate-banner {{ ($patientInfoCheck['has_placeholder_info'] ?? false) ? 'alert-danger' : 'alert-warning' }} border-0 mb-4 fade show" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas {{ ($patientInfoCheck['has_placeholder_info'] ?? false) ? 'fa-exclamation-circle' : 'fa-user-clock' }} fa-2x me-3 mt-1"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">
                            @if($patientInfoCheck['has_placeholder_info'] ?? false)
                                <strong class="text-danger">New patient — placeholder details must be replaced</strong>
                            @else
                                Please complete profile before clinical documentation
                            @endif
                        </h5>
                        <p class="mb-2">
                            Medical records and prescriptions stay unavailable until required details are updated.
                        </p>
                        <p class="mb-2 fw-semibold">Complete these items:</p>
                        <ul class="mb-3">
                            @foreach($patientInfoCheck['missing_fields'] as $field)
                                <li>
                                    @if(strpos($field, 'Requires completion') !== false || strpos($field, 'Requires valid email') !== false || strpos($field, 'placeholder') !== false || strpos($field, '@payment-link.temp') !== false)
                                        <strong class="text-danger">{{ $field }}</strong>
                                    @else
                                        {{ $field }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if(($patientInfoCheck['has_recommended_gaps'] ?? false) && count($patientInfoCheck['recommended_missing_fields'] ?? []))
                            <p class="mb-2 fw-semibold small text-muted">Recommended next:</p>
                            <ul class="mb-3 small">
                                @foreach($patientInfoCheck['recommended_missing_fields'] as $field)
                                    <li>{{ $field }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @include('staff.partials.guest-patient-actions', [
                            'patient' => $patient,
                            'primaryEmphasis' => (bool) ($patientInfoCheck['has_placeholder_info'] ?? false),
                        ])
                    </div>
                </div>
            </div>
        @elseif(($patientInfoCheck['has_recommended_gaps'] ?? false) && count($patientInfoCheck['recommended_missing_fields'] ?? []))
            <div class="alert alert-info border-0 mb-4 alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-2">Recommended patient details</h6>
                        <p class="mb-2 small">Core profile is complete. Consider adding:</p>
                        <ul class="mb-3 small">
                            @foreach($patientInfoCheck['recommended_missing_fields'] as $field)
                                <li>{{ $field }}</li>
                            @endforeach
                        </ul>
                        @include('staff.partials.guest-patient-actions', [
                            'patient' => $patient,
                            'primaryEmphasis' => false,
                        ])
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Personal Information</h5>
                </div>
                <div class="doctor-card-body">
                    @if($patient->photo)
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <img src="{{ $patient->photo_url }}" alt="Patient Photo" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            <div class="mt-2"><small class="text-muted">Patient Photo</small></div>
                        </div>
                    </div>
                    @endif

                    {{-- 1. Identity --}}
                    <div class="mb-4">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-id-card me-1"></i>Identity</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Patient ID</label>
                                <div class="fw-bold">{{ $patient->patient_id ?? 'Not assigned' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Full name</label>
                                <div class="fw-bold">{{ $patient->full_name }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Contact --}}
                    <div class="mb-4 pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-address-book me-1"></i>Contact</p>
                        <div class="row">
                            @if($patient->email)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Email</label>
                                <div class="fw-bold"><a href="mailto:{{ $patient->email }}" class="text-decoration-none">{{ $patient->email }}</a></div>
                            </div>
                            @endif
                            @if($patient->phone)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Phone</label>
                                <div class="fw-bold"><a href="tel:{{ $patient->phone }}" class="text-decoration-none">{{ $patient->phone }}</a></div>
                            </div>
                            @endif
                        </div>
                        @if(!$patient->email && !$patient->phone)
                        <div class="text-muted small">Not provided</div>
                        @endif
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Status</label>
                                <div class="fw-bold">
                                    @if($patient->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Demographics --}}
                    <div class="mb-4 pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-user me-1"></i>Demographics</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Date of birth</label>
                                <div class="fw-bold">
                                    {{ $patient->date_of_birth ? formatDate($patient->date_of_birth) : 'Not provided' }}
                                    @if($patient->date_of_birth)
                                        <small class="text-muted">({{ $patient->age }} years old)</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Gender</label>
                                <div class="fw-bold"><span class="badge bg-secondary">{{ ucfirst($patient->gender ?? 'Not specified') }}</span></div>
                            </div>
                        </div>
                    </div>

                    @php
                        $showGuardianContactStaff = ($patient->age !== null && (int) $patient->age < 18)
                            || filled($patient->guardian_name)
                            || filled($patient->guardian_phone);
                    @endphp
                    @if($showGuardianContactStaff)
                    <div class="mb-4 pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-user-shield me-1"></i>Parent / guardian</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Guardian / parent name</label>
                                <div class="fw-bold">{{ $patient->guardian_name ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Guardian / parent phone</label>
                                <div class="fw-bold">
                                    @if($patient->guardian_phone)
                                        <a href="tel:{{ $patient->guardian_phone }}" class="text-decoration-none">{{ $patient->guardian_phone }}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($patient->address || $patient->city || $patient->state || $patient->country || $patient->postal_code)
                    {{-- 4. Address --}}
                    <div class="mb-4 pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i>Address</p>
                        <div class="row">
                            @if($patient->full_address || $patient->address)
                            <div class="col-12">
                                <label class="form-label text-muted small mb-0">Street / address</label>
                                <div class="fw-bold">{{ $patient->full_address ?: $patient->address }}</div>
                            </div>
                            @endif
                            @if($patient->city || $patient->state || $patient->country || $patient->postal_code)
                            <div class="col-md-6">
                                @if($patient->city)
                                <label class="form-label text-muted small mb-0">City</label>
                                <div class="fw-bold">{{ $patient->city }}</div>
                                @endif
                                @if($patient->state)
                                <label class="form-label text-muted small mb-0">County</label>
                                <div class="fw-bold">{{ $patient->state }}</div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($patient->country)
                                <label class="form-label text-muted small mb-0">Country</label>
                                <div class="fw-bold">{{ $patient->country }}</div>
                                @endif
                                @if($patient->postal_code)
                                <label class="form-label text-muted small mb-0">Postcode</label>
                                <div class="fw-bold">{{ $patient->postal_code }}</div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- 5. Assigned clinic(s) --}}
                    @php
                        $patientDepartments = [];
                        if ($patient->relationLoaded('departments') || $patient->departments()->exists()) {
                            if (!$patient->relationLoaded('departments')) {
                                $patient->load('departments');
                            }
                            foreach ($patient->departments as $dept) {
                                $patientDepartments[] = ['name' => $dept->name, 'is_primary' => $dept->pivot->is_primary ?? false];
                            }
                        }
                        if (empty($patientDepartments) && $patient->department_id && $patient->department) {
                            if (!$patient->relationLoaded('department')) $patient->load('department');
                            if ($patient->department) {
                                $patientDepartments[] = ['name' => $patient->department->name, 'is_primary' => true];
                            }
                        }
                    @endphp
                    <div class="{{ ($patient->address || $patient->city || $patient->state || $patient->country || $patient->postal_code) ? 'pt-3 border-top' : '' }}">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-building me-1"></i>Assigned clinic(s)</p>
                        @if(!empty($patientDepartments))
                        <div>
                            @foreach($patientDepartments as $dept)
                            <div class="mb-1">
                                <span class="fw-bold">{{ $dept['name'] }}</span>
                                @if($dept['is_primary'] && count($patientDepartments) > 1)
                                    <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Primary</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-muted small">Not assigned</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Insurance Information -->
            @if($patient->insurance_provider || $patient->insurance_number)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Insurance Information</h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-shield-alt me-1"></i>Coverage</p>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Provider</label>
                            <div class="fw-bold">{{ $patient->insurance_provider ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Policy number</label>
                            <div class="fw-bold">{{ $patient->insurance_number ?? 'Not provided' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Medical Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-heartbeat me-2 text-primary"></i>Medical Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-4">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-allergies me-1"></i>Allergies & conditions</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Allergies</label>
                                <div class="fw-bold">
                                    @if($patient->allergies && count($patient->allergies) > 0)
                                        @foreach($patient->allergies as $allergy)
                                            <span class="badge bg-warning text-dark me-1 mb-1">{{ $allergy }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">None recorded</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Medical conditions</label>
                                <div class="fw-bold">
                                    @if($patient->medical_conditions && count($patient->medical_conditions) > 0)
                                        @foreach($patient->medical_conditions as $condition)
                                            <span class="badge bg-info text-dark me-1 mb-1">{{ $condition }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">None recorded</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($patient->notes)
                    <div class="pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-sticky-note me-1"></i>Notes</p>
                        <label class="form-label text-muted small mb-0">Additional notes</label>
                        <div class="border rounded p-3 bg-light mt-1">
                            {!! nl2br(e($patient->notes)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Uploaded Documents -->
            @if($patient->patient_id_document_path || $patient->guardian_id_document_path)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-file-upload me-2 text-primary"></i>Uploaded Documents</h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-file-alt me-1"></i>ID documents</p>
                    <div class="row">
                        @if($patient->patient_id_document_path)
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Patient ID document</label>
                            <div class="mt-1">
                                <a href="{{ route('staff.patients.download-document', ['patient' => $patient->id, 'type' => 'patient_id']) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-eye me-1"></i>View document
                                </a>
                                <small class="text-muted d-block mt-1">{{ basename($patient->patient_id_document_path) }}</small>
                            </div>
                        </div>
                        @endif
                        @if($patient->guardian_id_document_path)
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Guardian ID document</label>
                            <div class="mt-1">
                                <a href="{{ route('staff.patients.download-document', ['patient' => $patient->id, 'type' => 'guardian_id']) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-eye me-1"></i>View document
                                </a>
                                <small class="text-muted d-block mt-1">{{ basename($patient->guardian_id_document_path) }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- GP Information -->
            @if($patient->consent_share_with_gp || $patient->gp_name || $patient->gp_email)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2 text-primary"></i>GP (General Practitioner) Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-4">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-share-alt me-1"></i>Consent</p>
                        <label class="form-label text-muted small mb-0">Share with GP</label>
                        <div class="fw-bold">
                            @if($patient->consent_share_with_gp)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>
                    </div>
                    @if($patient->consent_share_with_gp && ($patient->gp_name || $patient->gp_email || $patient->gp_phone || $patient->gp_address))
                    <div class="pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-address-book me-1"></i>GP details</p>
                        <div class="row">
                            @if($patient->gp_name)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Name</label>
                                <div class="fw-bold">{{ $patient->gp_name }}</div>
                            </div>
                            @endif
                            @if($patient->gp_email)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Email</label>
                                <div class="fw-bold"><a href="mailto:{{ $patient->gp_email }}" class="text-decoration-none">{{ $patient->gp_email }}</a></div>
                            </div>
                            @endif
                            @if($patient->gp_phone)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Phone</label>
                                <div class="fw-bold"><a href="tel:{{ $patient->gp_phone }}" class="text-decoration-none">{{ $patient->gp_phone }}</a></div>
                            </div>
                            @endif
                            @if($patient->gp_address)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Address</label>
                                <div class="fw-bold">{{ $patient->gp_address }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Emergency Contact -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user-shield me-2 text-primary"></i>Emergency Contact</h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-phone-alt me-1"></i>Contact details</p>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Name</label>
                            <div class="fw-bold">{{ $patient->emergency_contact ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Phone</label>
                            <div class="fw-bold">
                                @if($patient->emergency_phone)
                                    <a href="tel:{{ $patient->emergency_phone }}" class="text-decoration-none">{{ $patient->emergency_phone }}</a>
                                @else
                                    Not provided
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Records -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-file-medical me-2 text-primary"></i>Medical Records
                        @if($patient->medicalRecords && $patient->medicalRecords->count() > 0)
                            <span class="badge bg-light text-dark ms-2">{{ $patient->medicalRecords->count() }}</span>
                        @endif
                    </h5>
                </div>
                    <div class="doctor-card-body">
                    @if($patient->medicalRecords && $patient->medicalRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Record Date</th>
                                        <th>Doctor</th>
                                        <th>Presenting Complaint</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->medicalRecords as $record)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ formatDate($record->record_date ?? $record->created_at) }}</div>
                                            <small class="text-muted">{{ ($record->record_date ?? $record->created_at)->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($record->doctor)
                                                <div class="fw-bold">{{ formatDoctorName($record->doctor->name ?? 'Unknown') }}</div>
                                                <small class="text-muted">{{ $record->doctor->specialization ?? 'GP' }}</small>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 250px;" title="{{ $record->presenting_complaint ?? $record->chief_complaint ?? 'N/A' }}">
                                                {{ Str::limit($record->presenting_complaint ?? $record->chief_complaint ?? 'N/A', 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'consultation' => 'primary',
                                                    'follow_up' => 'info',
                                                    'emergency' => 'danger',
                                                    'routine_checkup' => 'success',
                                                    'procedure' => 'warning'
                                                ];
                                                $typeColor = $typeColors[$record->record_type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $typeColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $record->record_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('staff.medical-records.show', $record) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Record">
                                                <i class="fas fa-eye me-1"></i>View Record
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($patient->medicalRecords->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('staff.medical-records.index', ['patient_search' => $patient->first_name . ' ' . $patient->last_name]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>View All Medical Records
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No medical records found for this patient.</p>
                            @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                                <a href="{{ route('staff.medical-records.create', ['patient_id' => $patient->id]) }}" class="btn btn-doctor-primary mt-3">
                                    <i class="fas fa-plus me-1"></i>Create First Medical Record
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            @can('viewAny', [\App\Models\PatientDocument::class, $patient])
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>Letters & Forms
                            @if(isset($documents) && $documents->count() > 0)
                                <span class="badge bg-light text-dark ms-2">{{ $documents->count() }}</span>
                            @endif
                        </h5>
                        @can('create', [\App\Models\PatientDocument::class, $patient])
                        <a href="{{ route('staff.patients.documents.create', $patient) }}" class="btn btn-sm btn-doctor-primary">
                            <i class="fas fa-plus me-1"></i>Add Document
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="doctor-card-body">
                    @php
                        // Debug: Check if documents variable exists
                        $hasDocuments = isset($documents) && $documents instanceof \Illuminate\Support\Collection && $documents->count() > 0;
                        // Also check total documents count for this patient (for admin info)
                        $totalDocumentsCount = null;
                        if (auth()->user()->is_admin || auth()->user()->role === 'admin') {
                            $totalDocumentsCount = $patient->documents()->count();
                        }
                    @endphp
                    @if($hasDocuments)
                        <div class="list-group list-group-flush">
                            @foreach($documents as $document)
                            <div class="list-group-item border-0 px-0 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-{{ $document->type === 'letter' ? 'file-alt' : 'list' }} me-2 text-primary"></i>
                                            <h6 class="mb-0 fw-bold">{{ $document->title }}</h6>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'warning',
                                                    'final' => 'success',
                                                    'void' => 'danger'
                                                ];
                                                $statusColor = $statusColors[$document->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }} ms-2">{{ ucfirst($document->status) }}</span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar me-1"></i>{{ formatDateUk($document->created_at) }}
                                            @if($document->creator)
                                                <span class="ms-2"><i class="fas fa-user me-1"></i>{{ $document->creator->name }}</span>
                                            @endif
                                            @if($document->type)
                                                <span class="ms-2"><i class="fas fa-tag me-1"></i>{{ ucfirst($document->type) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <a href="{{ route('staff.patients.documents.show', [$patient, $document]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Document">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($document->pdf_path)
                                        <a href="{{ route('staff.patients.documents.download', [$patient, $document]) }}" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('staff.patients.documents.index', $patient) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i>View All Documents
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">
                                @if($totalDocumentsCount !== null && $totalDocumentsCount > 0)
                                    No documents visible to you. (Total: {{ $totalDocumentsCount }} - you can only see documents you created)
                                @else
                                    No documents found for this patient.
                                @endif
                            </p>
                            @can('create', [\App\Models\PatientDocument::class, $patient])
                            <a href="{{ route('staff.patients.documents.create', $patient) }}" class="btn btn-doctor-primary mt-3">
                                <i class="fas fa-plus me-1"></i>Create First Document
                            </a>
                            @endcan
                            <div class="mt-2">
                                <a href="{{ route('staff.patients.documents.index', $patient) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-list me-1"></i>View All Documents
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Appointments -->
            @if($patient->appointments && $patient->appointments->count() > 0)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Recent Appointments</h5>
                </div>
                    <div class="doctor-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->appointments->take(5) as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date ? formatDate($appointment->appointment_date) : 'N/A' }}</td>
                                    <td>{{ $appointment->appointment_time ?? 'N/A' }}</td>
                                    <td>
                                        @if($appointment->doctor)
                                            <div class="fw-bold">{{ formatDoctorName($appointment->doctor->name ?? 'Unknown') }}</div>
                                            <small class="text-muted">{{ $appointment->doctor->specialization ?? 'GP' }}</small>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($appointment->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($appointment->appointment_type ?? 'consultation') }}</td>
                                    <td>
                                        <a href="{{ route('staff.appointments.show', $appointment) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Appointment">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($patient->appointments->count() > 5)
                        <div class="text-center mt-3">
                            <small class="text-muted">Showing 5 most recent appointments</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
                </div>
                <div class="doctor-card-body">
                    @if(auth()->user()->role === 'doctor')
                        <h6 class="text-uppercase fw-bold text-muted small mb-2">Workflow</h6>
                        <div class="d-grid gap-2 mb-3">
                            <a href="{{ route('staff.appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-doctor-primary w-100">
                                <i class="fas fa-calendar-plus me-2"></i>Schedule Appointment
                            </a>
                            
                            @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                                <a href="{{ route('staff.medical-records.create', ['patient_id' => $patient->id]) }}" class="btn btn-success w-100">
                                    <i class="fas fa-file-medical me-2"></i>New Medical Record
                                </a>
                            @endif
                            
                            @if(auth()->user()->role === 'doctor')
                                <a href="{{ route('staff.prescriptions.create', ['patient_id' => $patient->id]) }}" class="btn btn-info w-100">
                                    <i class="fas fa-prescription-bottle-alt me-2"></i>Write Prescription
                                </a>
                                
                                <a href="{{ route('staff.lab-reports.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-info w-100">
                                    <i class="fas fa-vial me-2"></i>Order Lab Test
                                </a>
                                
                                @if($patient->consent_share_with_gp && $patient->gp_email)
                                <a href="{{ route('staff.patients.gp-email', $patient) }}" class="btn btn-success w-100">
                                    <i class="fas fa-user-md me-2"></i>Contact GP
                                </a>
                                @endif
                            @endif
                        </div>
                        
                        <div class="dropdown-divider my-2"></div>
                    @endif
                    
                    @can('viewAny', [\App\Models\PatientAlert::class, $patient])
                    <h6 class="text-uppercase fw-bold text-muted small mb-2">Alerts</h6>
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('staff.patients.alerts.index', $patient) }}" class="btn btn-outline-danger w-100">
                            <i class="fas fa-exclamation-triangle me-2"></i>View Alerts
                            @php
                                $activeAlertsCount = $patient->activeAlerts()->count();
                            @endphp
                            @if($activeAlertsCount > 0)
                                <span class="badge bg-danger ms-2">{{ $activeAlertsCount }}</span>
                            @endif
                        </a>
                        @can('create', [\App\Models\PatientAlert::class, $patient])
                        <a href="{{ route('staff.patients.alerts.create', $patient) }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-plus me-2"></i>Add Alert
                        </a>
                        @endcan
                    </div>
                    <div class="dropdown-divider my-2"></div>
                    @endcan
                    
                    @can('viewAny', [\App\Models\PatientDocument::class, $patient])
                    <h6 class="text-uppercase fw-bold text-muted small mb-2">Letters & Forms</h6>
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('staff.patients.documents.index', $patient) }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-file-alt me-2"></i>View Documents
                        </a>
                        @can('create', [\App\Models\PatientDocument::class, $patient])
                        <a href="{{ route('staff.patients.documents.create', $patient) }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-plus me-2"></i>Create Document
                        </a>
                        @endcan
                    </div>
                    <div class="dropdown-divider my-2"></div>
                    @endcan
                    
                    <h6 class="text-uppercase fw-bold text-muted small mb-2">Management</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.patients.edit', $patient) }}" class="btn btn-warning w-100">
                            <i class="fas fa-edit me-2"></i>Edit Patient
                        </a>
                        <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Quick Stats</h5>
                </div>
                    <div class="doctor-card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-primary">{{ $patient->medicalRecords ? $patient->medicalRecords->count() : 0 }}</div>
                            <small class="text-muted">Medical Records</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-success">{{ $patient->appointments ? $patient->appointments->count() : 0 }}</div>
                            <small class="text-muted">Total Appointments</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-1 text-warning">{{ $patient->appointments ? $patient->appointments->where('status', 'pending')->count() : 0 }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-1 text-success">{{ $patient->appointments ? $patient->appointments->where('status', 'completed')->count() : 0 }}</div>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-semibold">Patient Information</h6>
                </div>
                    <div class="doctor-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Registered</small>
                        <strong>{{ $patient->created_at ? formatDateUk($patient->created_at) : 'Unknown' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <strong>{{ $patient->updated_at ? $patient->updated_at->diffForHumans() : 'Never' }}</strong>
                    </div>
                    @if($patient->notes)
                    <div class="mb-0">
                        <small class="text-muted d-block">Notes</small>
                        <p class="small mb-0">{{ $patient->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Stays under sticky layout header; remains visible while scrolling until profile is complete */
    .clinical-profile-gate-banner {
        position: sticky;
        top: var(--header-height, 70px);
        z-index: 1100;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 30s (not clinical profile gate / persistent)
    setTimeout(function() {
        $('.alert:not(.alert-persistent)').fadeOut();
    }, 30000);
});
</script>
@endpush
